<?php

namespace App\Http\Controllers\Web\User\Auth;

use App\Enums\User\State;
use App\Models\User;
use App\Services\FirebaseService\FirebaseTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Employee;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserLoginController extends Controller
{
    // Définir le nom du gardien par défaut
    private $guard_name = 'web';

    /**
     * Définissez le chemin de redirection par défaut après la connexion(login).
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    // Initialiser le service FirebaseTopic
    protected FirebaseTopic $topic;

    public function __construct()
    {
        // Initialiser l'instance FirebaseTopic
        $this->topic = (new FirebaseTopic());
        //Appliquer le middleware « guest » à toutes les méthodes sauf « logout »

        $this->middleware('guest:web')->except('logout');
    }

    /**
     *  Afficher le formulaire de connexion
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('landing.auth.login');
    }

    /**
     *  Gérer la tentative de connexion de l'utilisateur
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // Valider les entrées utilisateur en utilisant le système de validation de Laravel et les règles définies dans LoginRequest.php. (public function rules())

        $data = $request->validated();

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $data['email'])->first() ?? Employee::where('email', $data['email'])->first();

        // Si l'utilisateur n'est pas trouvé, revenez à la page de connexion avec un message d'erreur
        if (!$user) {
            return $this->loginFailed($request);
        }

        if ($user instanceof Employee) {
            $this->guard_name = "employee";
        }

        //Vérifiez l'état de l'utilisateur et revenez à la page de connexion avec un message d'erreur
        switch ($user->state) {
            case State::PENDING:
                return $this->loginFailed($request, trans('auth.state.pending'));
            case State::INACTIVE:
                return $this->loginFailed($request, trans('auth.state.inactive'));
        }

        // Si la connexion réussit, procédez à la connexion et redirigez vers la page prévue

        if ($this->guard()->attempt($request->only(['email', 'password']), $request->has('remember_me'))) {
            $this->processLogin($data);
            return redirect()->intended($this->redirectTo);
        }


        // handel si toutes les conditions ci-dessus échouent, redirigez vers la page de connexion
        return $this->loginFailed($request);
    }

    /**
     * Gérer la déconnexion des utilisateurs
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        if (Auth::guard('employee')->check()) {
            $this->guard_name = "employee";
        }

        if ($this->guard()->user()->has_topic && $request->filled('fcm_token')) {
            $this->processLogout($request);
        }

        //Effectuer des actions de déconnexion et rediriger vers la page d'accueil
        $this->performLogoutActions($request);
        return redirect()->route('home');
    }


    private function guard()
    {
        return Auth::guard($this->guard_name);
    }


    private function processLogin($data)
    {
        if ($this->guard()->user()->profile) {
            Session::put('profile', $this->guard()->user()->profile);
        }

        if ($data['fcm_token'] !== null) {
            $result = $this->topic->subscribe($this->guard()->user()->profile->slug, $data['fcm_token']);
            if ($result) {
                $this->guard()->user()->updateQuietly([
                    'has_topic' => true,
                    'topic_subscriptions_count' => $this->guard()->user()->topic_subscriptions_count + 1,
                ]);
            }
        }
    }

    private function loginFailed($request, $message = null)
    {
        session()->flash('login_error', $message ?? trans('auth.failed'));
        return redirect()->back()->withInput($request->input());
    }

    private function processLogout($request)
    {
        $count = $this->guard()->user()->topic_subscriptions_count;
        $this->topic->unsubscribe($request->get('fcm_token'));
        $this->guard()->user()->update([
            'has_topic' => $count - 1 > 0,
            'topic_subscriptions_count' => max(0, $count - 1),
        ]);
    }

    // Perform logout actions
    private function performLogoutActions($request)
    {
        $lang = $this->guard()->user()->lang;

        session()->forget('profile');
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->put('locale', $lang);
    }

}