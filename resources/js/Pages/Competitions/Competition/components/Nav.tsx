
import { Link } from "@inertiajs/inertia-react";
import DropdownDefault from "@/components/DropdownDefault";
import request from "@/utils/request";

interface CompetitionInterface {
    id: string;
    name: string;
    slug: string;
    status: string;
}

interface Props {
    title: string;
    competition: CompetitionInterface | undefined;
    setCompetition: any;
}

const Nav = ({ title, competition, setCompetition }: Props) => {

    function changeStatus() {

        if (competition?.id)
            request.post(`/competitions/competition/${competition.id}/change-status`).then(resp => {
                const { data } = resp

                if (data?.competition)
                    setCompetition(data.competition);
            })
    }

    return (
        <div>
            {competition &&
                <div className="flex justify-between w-full mb-4">
                    <h4 className="text-lg font-bold">
                        {`${competition.name} ${title}`}
                    </h4>
                    <DropdownDefault text="Competition Actions">
                        <ul>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/competitions/competition/${competition.id}`}>Teams</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/competitions/competition/${competition.id}/predictions`}>Predictions</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/competitions/competition/${competition.id}/fixtures`}>Fixtures</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/competitions/competition/${competition.id}/detailed-fixtures`}>Detailed Fixtures</Link></li>
                            <li><Link className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4 block" href={`/competitions/competition/${competition.id}/update`}>Update</Link></li>
                            <li className="rounded-sm py-1.5 px-4 cursor-pointer text-sm hover:bg-gray dark:hover:bg-meta-4" onClick={changeStatus}>{competition.status == '1' ? 'Disable' : 'Enable'}</li>
                        </ul>
                    </DropdownDefault>
                </div>
            }

        </div>
    );
};

export default Nav;
