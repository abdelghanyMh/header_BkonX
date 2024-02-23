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
    private $guard_name = 'web' ;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;
    protected FirebaseTopic $topic;

    public function __construct()
    {
        $this->topic = (new FirebaseTopic());
        $this->middleware('guest:web')->except('logout');
    }

    /**
     * return login form for user
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('landing.auth.login');
    }

    /**
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::where('email',$data['email'])->first() ?? Employee::where('email',$data['email'])->first();

        if ($user)
        {
            if($user instanceof Employee){
                $this->guard_name="employee" ;
            }

            switch ($user->state) {

                case State::PENDING:
                    session()->flash('login_error',trans('auth.state.pending'));
                    return redirect()->back();
                    break;

                case State::INACTIVE:
                    session()->flash('login_error',trans('auth.state.inactive'));
                    return redirect()->back();
                    break;
            }
        
                // login success
                if ($this->guard()->attempt($request->only(['email','password']),$request->has('remember_me'))){

                    if ($this->guard()->user()->profile)
                    {
                        Session::put('profile',$this->guard()->user()->profile);
                    }
        
                     if($data['fcm_token'] != null)
                     {
                         $result = $this->topic->subscribe($this->guard()->user()->profile->slug,$data['fcm_token']);

                         if($result)
                         {
                             $this->guard()->user()->updateQuietly(
                                 [
                                     'has_topic' => true,
                                     'topic_subscriptions_count' => $this->guard()->user()->topic_subscriptions_count + 1,
                                 ]
                             );
                         }
                     }
        
                    return redirect()->intended($this->redirectTo);
        
                }
        
        }



        //login fails
        session()->flash('login_error',trans('auth.failed'));
        return redirect()->back()->withInput($request->input());
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        if(Auth::guard('employee')->check())
        {
            $this->guard_name="employee";
        }
        
        if ($this->guard()->user()->has_topic)
        {
            $count = $this->guard()->user()->topic_subscriptions_count;

            if($request->get('fcm_token'))
            {
                $this->topic->unsubscribe($request->get('fcm_token'));
                if($count - 1 <= 0)
                {
                    $this->guard()->user()->update(
                        [
                            'has_topic' => false,
                            'topic_subscriptions_count' => 0,
                        ]
                    );
                }
                else
                {
                    $this->guard()->user()->update(
                        [
                            'topic_subscriptions_count' => $count - 1,
                        ]
                    );
                }
            }
        }

        $lang = $this->guard()->user()->lang;

        session()->forget('profile');

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        session()->put('locale', $lang);

        if($request->wantsJson())
        {
            return response()->json(__('messages.flash.success'));
        }

        return to_route('home');
    }

    private function guard()
    {
        return Auth::guard($this->guard_name);
    }

}

