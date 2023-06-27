import { usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "@/layout/DefaultLayout";
import request from "@/utils/request";
import { useEffect, useState } from "react";
import Nav from "./components/Nav";
import FixturesDetails from "@/Pages/Competitions/Competition/components/FixturesDetails.tsx";
import GamesComponent from "./components/GamesComponent";
import FetchControls from "@/components/FetchControls";

interface GameInterface {
    id: string;
    date: string;
    home_team: string;
    away_team: string;
    competition: string;
    ht_results: string;
    ft_results: string;
    fetching_fixture_state: string;
    updated_at: string;
    created_at: string;
    last_fetch: string
    action: string;
}

interface TeamInterface {
    id: string;
    name: string;
    slug: string;
    games: [GameInterface];
    recentlyFetchedGames: [GameInterface];
    status: string;
    last_fetch: string;
}

interface Res {

}

const Fixtures = () => {
    const { props } = usePage<any>();

    const [team, setTeam] = useState<TeamInterface>()

    useEffect(() => {
        let { team: tmp } = props

        setTeam(tmp)
        setLimitList(generateArray(tmp.games.length || 0, 0));

    }, [props.team])


    const [source, setSource] = useState('')

    const [res, setRes] = useState<Res[]>()
    const [message, setMessage] = useState('')
    const [limitList, setLimitList] = useState<number[]>([]);
    const [limit, setLimit] = useState<number>(0);

    const handleSubmit = (e: any) => {
        e.preventDefault()

        if (team)
            request.post(`/teams/team/${team.id}/fixtures`, { limit }).then(function (resp) {
                const { data } = resp

                if (data?.message)
                    setMessage(data.message)
                else {
                    setMessage('')
                    setRes((curr: Res[] | undefined) => curr ? [...curr, ...[data]] : [data]);
                }
            })
    }

    function generateArray(length: number, startAt: number) {
        return Array.from({ length: length }, (_, index) => startAt + index);
    }

    return (
        <DefaultLayout>
            <div>
                <Nav title="Fixtures" team={team} setTeam={setTeam} />
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark p-2">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Get team fixtures</h3>
                    </div>
                    <div className="p-6.5">
                        <form action="#" onSubmit={handleSubmit}>
                            <div className="mb-4">
                                {team && <FetchControls item={team} limitList={limitList} setLimit={setLimit} />}
                            </div>
                        </form>
                        {message && <div className="my-2 border p-2 rounded">{message}</div>}

                        <div className="flex gap-3">
                            <div className="w-1/2">
                                {team &&
                                    <div>
                                        <div className="mb-4">
                                            <h5 className="font-bold">Untouched games</h5>
                                            <div className="ml-4">
                                                <GamesComponent games={team.games} />
                                            </div>
                                        </div>
                                        <div className="mb-4">
                                            <h5 className="font-bold">Recent games</h5>
                                            <div className="ml-4">
                                                <GamesComponent games={team.recentlyFetchedGames} />
                                            </div>
                                        </div>
                                    </div>
                                }
                            </div>
                            <div className="w-1/2">
                                {res && res.map((team: any, i) =>
                                    <div key={i}>
                                        <FixturesDetails numbered={true} items={team} />
                                    </div>
                                )}
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </DefaultLayout>
    );
};

export default Fixtures;
