import * as React from "react";

import { cn } from "@/lib/utils";
import { ArrowRightCircle } from "lucide-react";

import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
} from "@/components/ui/navigation-menu";

const components: { title: string; href: string; description: string }[] = [
  {
    title: "Communauté",
    href: "#",
    description:
      "Interact with the ecosystem, find other entrepreneurs, experts and investors.",
  },
  {
    title: "Formation",
    href: "#",
    description: "Publish your content to be visible to the community",
  },
  {
    title: "Trouvez un expert",
    href: "#",
    description: "Publish your content to be visible to the community",
  },
  {
    title: "Monétisation de l'Expertise",
    href: "#",
    description: "Publish your content to be visible to the community",
  },
];
export function NavigationMenuDemo() {
  return (
    <NavigationMenu>
      <NavigationMenuList>
        <NavigationMenuItem className="mx-3">
          <a href="#">
            <NavigationMenuLink>Accueil</NavigationMenuLink>
          </a>
        </NavigationMenuItem>
        <NavigationMenuItem>
          <a href="#">
            <NavigationMenuLink>À propos</NavigationMenuLink>
          </a>
        </NavigationMenuItem>

        <NavigationMenuItem>
          <NavigationMenuTrigger>Membre</NavigationMenuTrigger>
          <NavigationMenuContent>
            <ul className="grid w-[100vw] gap-3 p-4  grid-cols-4  ">
              {components.map((component) => (
                <ListItem
                  key={component.title}
                  title={component.title}
                  href={component.href}
                >
                  {component.description}
                </ListItem>
              ))}
            </ul>
          </NavigationMenuContent>
        </NavigationMenuItem>
        <NavigationMenuItem>
          <NavigationMenuTrigger>Expert</NavigationMenuTrigger>
          <NavigationMenuContent>
            <ul className="grid w-[100vw] gap-3 p-4  grid-cols-4  ">
              {components.map((component) => (
                <ListItem
                  key={component.title}
                  title={component.title}
                  href={component.href}
                >
                  {component.description}
                </ListItem>
              ))}
            </ul>
          </NavigationMenuContent>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenu>
  );
}

const ListItem = React.forwardRef<
  React.ElementRef<"a">,
  React.ComponentPropsWithoutRef<"a">
>(({ className, title, children, ...props }, ref) => {
  return (
    <li>
      <NavigationMenuLink asChild>
        <a
          ref={ref}
          className={cn(
            "block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground",
            className
          )}
          {...props}
        >
          <div className="text-sm font-medium leading-none flex flex-row gap-2 items-center mb-4">
            {title} <ArrowRightCircle />
          </div>
          <p className="line-clamp-2 text-sm leading-snug text-muted-foreground">
            {children}
          </p>
        </a>
      </NavigationMenuLink>
    </li>
  );
});
ListItem.displayName = "ListItem";
