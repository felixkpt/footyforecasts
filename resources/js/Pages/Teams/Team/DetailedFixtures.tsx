import { usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "@/layout/DefaultLayout";
import request from "@/utils/request";
import { useEffect, useState } from "react";
import Nav from "./components/Nav";
import DetailedFixturesDetails from "./components/DetailedFixturesDetails";
import TimeAgo from 'timeago-react';
import DetailedFetchControls from "@/components/DetailedFetchControls";

interface GameInterface {
    id: string;
    team_abbreviation: string;
    slug: string;
    action: string;
    fetching_fixture_state: number;
}

interface TeamInterface {
    id: string;
    name: string;
    slug: string;
    games: [GameInterface];
    recentlyFetchedGames: [GameInterface];
    status: string;
    last_fetch: string
    last_detailed_fetch: string;
}

interface Res {

}

const DetailedFixtures = () => {
    const { props } = usePage<any>();

    const [team, setTeam] = useState<TeamInterface>()

    useEffect(() => {
        let { team: tmp } = props

        setTeam(tmp)
        setLimitList(generateArray(tmp.games.length || 0, 0));

    }, [props.team])


    const [res, setRes] = useState<Res[]>()
    const [message, setMessage] = useState('')
    const [limitList, setLimitList] = useState<number[]>([]);
    const [limit, setLimit] = useState<number>(0);

    const handleSubmit = (e: any) => {
        e.preventDefault()

        if (team)
            request.post(`/teams/team/${team.id}/detailed-fixtures`, { limit }).then(function (resp) {
                const { data } = resp

                if (data?.message)
                    setMessage(data.message)
                else {
                    setMessage('')
                    setRes((curr: Res[] | undefined) => curr ? [...curr, ...data.res] : data.res);
                    setTeam(data.team)
                }
            })
    }

    function generateArray(length: number, startAt: number) {
        return Array.from({ length: length }, (_, index) => startAt + index);
    }

    return (
        <DefaultLayout>
            <div>
                <Nav title="Detailed Fixtures" team={team} setTeam={setTeam} />
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark p-2">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Get team detailed fixtures</h3>
                    </div>
                    <div className="p-6.5">
                        <form action="#" onSubmit={handleSubmit}>
                            <div className="mb-4">
                            {team && <DetailedFetchControls item={team} limitList={limitList} setLimit={setLimit} />}
                            </div>
                        </form>
                        {message && <div>{message}</div>}

                        <div className="flex gap-3">
                            <div className="w-1/2">
                                {team &&
                                    <div className="mb-4">
                                        <h5 className="font-bold">We will get fixtures for</h5>
                                        <div className="ml-4">
                                            {team.games.map((game: GameInterface, i) => (
                                                <div className="flex gap-2" key={game.id}>
                                                    {i + 1}.<div className="flex gap-2"><span>{`#${game.id} -- ${game.team_abbreviation || 'no abbrv tag'},`}</span><span>(Fetch state: {game.fetching_fixture_state})</span></div>
                                                </div>
                                            ))}
                                            {team.games.length < 1 && 'List is empty!'}
                                        </div>
                                    </div>
                                }

                                {team &&
                                    <div className="mb-4">
                                        <h5 className="font-bold">Recently updated</h5>
                                        <div className="ml-4">
                                            {team.recentlyFetchedGames.map((game: GameInterface, i) => (
                                                <div className="flex gap-2" key={game.id}>
                                                    {i + 1}.<div className="flex gap-2"><span>{`#${game.id} -- ${game.team_abbreviation || 'no abbrv tag'},`}</span><span>(Fetch state: {game.fetching_fixture_state})</span></div>
                                                </div>
                                            ))}
                                            {team.recentlyFetchedGames.length < 1 && 'List is empty!'}
                                        </div>
                                    </div>

                                }
                            </div>
                            <div className="w-1/2">
                                {res && res.map((itm: any, i) =>
                                    <div key={i}>
                                        {
                                            Object.keys(itm).map(
                                                (key: any, j) =>
                                                    <div key={j}>
                                                        {typeof itm[key] === 'string' ? <b>{i + 1}. {itm[key]}</b> : <DetailedFixturesDetails items={itm.fetch_details} />}
                                                    </div>
                                            )
                                        }
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

export default DetailedFixtures;
