import { usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "@/layout/DefaultLayout";
import request from "@/utils/request";
import { useEffect, useState } from "react";
import Nav from "./components/Nav";
import DetailedFixturesDetails from "./components/DetailedFixturesDetails";
import TimeAgo from "timeago-react";
import DetailedFetchControls from "@/components/DetailedFetchControls";

interface GameInterface {
    id: string;
    competition_abbreviation: string;
    slug: string;
    last_fetch: string
    action: string;
    fetching_fixture_state: number;
}

interface CompetitionInterface {
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

const DetailedFixtures = () => {
    const { props } = usePage<any>();

    const [competition, setCompetition] = useState<CompetitionInterface>()

    useEffect(() => {
        let { competition: compe } = props

        setCompetition(compe)
        setLimitList(generateArray(compe.games.length || 0, 0));

    }, [props.competition])

    const [res, setRes] = useState<Res[]>()
    const [message, setMessage] = useState('')
    const [limitList, setLimitList] = useState<number[]>([]);
    const [limit, setLimit] = useState<number>(0);

    const handleSubmit = (e: any) => {
        e.preventDefault()

        if (competition)
            request.post(`/competitions/competition/${competition.id}/detailed-fixtures`, { limit }).then(function (resp) {
                const { data } = resp

                if (data?.message)
                    setMessage(data.message)
                else {
                    setMessage('')
                    setRes((curr: Res[] | undefined) => curr ? [...curr, ...data.res] : data.res);
                    setCompetition(data.competition)
                }
            })
    }


    function generateArray(length: number, startAt: number) {
        return Array.from({ length: length }, (_, index) => startAt + index);
    }

    return (
        <DefaultLayout>
            <div>
                <Nav title="Detailed Fixtures" competition={competition} setCompetition={setCompetition} />
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark p-2">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Get competition detailed fixtures</h3>
                    </div>
                    <div className="p-6.5">
                        <form action="#" onSubmit={handleSubmit}>
                            <div className="mb-4">
                                {competition && <DetailedFetchControls item={competition} limitList={limitList} setLimit={setLimit} />}
                            </div>
                        </form>
                        {message && <div>{message}</div>}

                        <div className="flex gap-3">
                            <div className="w-1/2">
                                {competition &&
                                    <div className="mb-4">
                                        <h5 className="font-bold">We will get fixtures for</h5>
                                        <div className="ml-4">
                                            {competition.games.map((game: GameInterface, i) => (
                                                <div className="flex gap-2" key={game.id}>
                                                    {i + 1}.<div className="flex gap-2"><span>{`#${game.id} -- ${game.competition_abbreviation},`}</span><span>(Fetch state: {game.fetching_fixture_state})</span></div>
                                                </div>
                                            ))}
                                            {competition.games.length < 1 && 'List is empty!'}
                                        </div>
                                    </div>
                                }

                                {competition &&
                                    <div className="mb-4">
                                        <h5 className="font-bold">Recently updated</h5>
                                        <div className="ml-4">
                                            {competition.recentlyFetchedGames.map((game: GameInterface, i) => (
                                                <div className="flex gap-2" key={game.id}>
                                                    {i + 1}.<div className="flex gap-2"><span>{`#${game.id} -- ${game.competition_abbreviation},`}</span><span>(Fetch state: {game.fetching_fixture_state})</span></div>
                                                </div>
                                            ))}
                                            {competition.recentlyFetchedGames.length < 1 && 'List is empty!'}
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
