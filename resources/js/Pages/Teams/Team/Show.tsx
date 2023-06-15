import { Link, usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "../../../layout/DefaultLayout";

const Show = () => {

    const { props } = usePage<any>();
    const { team } = props
    return (
        <DefaultLayout>
            {team &&
                <div className="flex justify-between w-full">
                    <div className="ml-4">
                        {team.name}
                    </div>
                    <div><Link href={`/teams/team/${team.id}/actions`}>Team Actions</Link></div>
                </div>
            }
        </DefaultLayout>
    );
};

export default Show;
