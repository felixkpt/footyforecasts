
import { Link } from "@inertiajs/inertia-react";
import DropdownDefault from "@/components/DropdownDefault";
import request from "@/utils/request";

interface TeamInterface {
    id: string;
    name: string;
    slug: string;
    games: any
}
interface Props {
    title: string;
    team: TeamInterface | undefined;
    setTeam: any;
}

const Nav = ({ title, team, setTeam }: Props) => {

    function changeStatus() {

        if (team?.id)
            request.post(`/teams/team/${team.id}/change-status`).then(resp => {
                const { data } = resp

                if (data?.team)
                    setTeam(data.team);
            })
    }

    return (
        <div>
            {team &&
                <div className="flex justify-between w-full mb-4">
                    <h4 className="text-lg font-bold">
                        {`${team.name} ${title}`}
                    </h4>
                    <DropdownDefault text="Team Actions">
                        <ul>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/teams/team/${team.id}`}>Games</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/teams/team/${team.id}/predictions`}>Predictions</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/teams/team/${team.id}/fixtures`}>Fixtures</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/teams/team/${team.id}/detailed-fixtures`}>Detailed Fixtures</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/teams/team/${team.id}/update`}>Update</Link></li>
                            <li className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4" onClick={changeStatus}>{team.status == '1' ? 'Disable' : 'Enable'}</li>
                        </ul>
                    </DropdownDefault>
                </div>
            }

        </div>
    );
};

export default Nav;
