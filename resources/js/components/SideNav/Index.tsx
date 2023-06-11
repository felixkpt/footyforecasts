import SideNavItem from "./SideNavItem"
import items from '../../data/sidenav.json'
import { useRef } from "react";
import Header from "./Header";

interface SidebarProps {
  sidebarOpen: boolean;
  setSidebarOpen: (arg: boolean) => void;
}

const Sidebar = ({ sidebarOpen, setSidebarOpen }: SidebarProps) => {

  const sidebar = useRef<any>(null);

  return (
    <aside
      ref={sidebar}
      className={`absolute left-0 top-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-hidden bg-black duration-300 ease-linear dark:bg-boxdark lg:static lg:translate-x-0 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
    >
      <Header sidebar={sidebar} setSidebarOpen={setSidebarOpen} sidebarOpen={sidebarOpen} />
      <div className="sidebar">
        {items.map((item, index) => <SideNavItem key={index} item={item} />)}
      </div>
    </aside>
  )
}

export default Sidebar