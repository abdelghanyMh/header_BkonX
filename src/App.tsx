import "./App.css";
import {} from "module";
import { NavigationMenuDemo } from "./components/NavigationMenuDemo";
import Container from "./components/ui/container";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";

function App() {
  return (
    <div className=" flex  h-screen flex-col">
      <header className="border-b px-4 py-3 sm:flex sm:justify-between items-center">
        <Container>
          <NavigationMenuDemo />
        </Container>
        <div className="w-216px">
          <img src="/logo.png" alt="" className="h-full" />
        </div>
        <div className="flex items-center gap-3">
          <a href="#" className="border-r-1">
            FAQ
          </a>
          <Separator className="w-1 h-12" orientation="vertical" />

          <a href="#" className="text-primary">
            connexion
          </a>
          <Button asChild>
            <a href="/login">Rejoignez la communauté</a>
          </Button>
        </div>
      </header>
    </div>
  );
}

export default App;
