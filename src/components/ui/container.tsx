import { cn } from "@/lib/utils";
import * as React from "react";
interface ContainerProps {
  children: React.ReactNode;
  className?: string;
}

const Container = ({
  children,
  className,
}: ContainerProps): React.JSX.Element => (
  <div className={cn(className)}>{children}</div>
);

export default Container;
