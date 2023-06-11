import { Key, useState } from "react"
import { Icon } from '@iconify-icon/react';
import { Link } from "@inertiajs/inertia-react";

interface SidebarChildItemInterface {
    item: any,
    title: string,
    icon: string,
    path: string,
    children: any,

}

interface SidebarItemInterface {
    item: {
        title: string,
        icon: any,
        children: [
            SidebarChildItemInterface
        ]
    }
    path: string,
}
export default function SidebarItem({ item, path }: SidebarItemInterface | SidebarChildItemInterface) {
    const [open, setOpen] = useState(false)

    if (item.children) {
        return (
            <div className={open ? "sidebar-item open" : "sidebar-item"}>
                <div className="sidebar-title" onClick={() => setOpen(!open)}>
                    <span>
                        {item.icon && <span className="mr-1"><Icon icon={item.icon} /></span>}
                        {item.title}
                    </span>
                    <Icon icon="bi-chevron-down" />
                </div>
                <div className="sidebar-content">
                    {item.children.map((child: any | { title: string; icon: any; children: [SidebarChildItemInterface]; }, index: Key | null | undefined) => <SidebarItem key={index} item={child} path={item.path} />)}
                </div>
            </div>
        )
    } else {
        return (
            <Link href={(path + '/' + item.path).replace(/\/+/g, '/') || "#"} className="sidebar-item plain">
                {item.icon && <span className="mr-1"><Icon icon={item.icon} /></span>}
                {item.title}
            </Link>
        )
    }
}