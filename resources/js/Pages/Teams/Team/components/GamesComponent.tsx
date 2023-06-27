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

type Props = {
    games: GameInterface[]
}

export default function GamesComponent({ games }: Props) {
    function formatDate(date: any) {

        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [day, month, year].join('/');
    }

    return (
        <div>
            <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table className="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" className="px-1 py-3">#</th>
                            <th scope="col" className="px-2 py-3">Date</th>
                            <th scope="col" className="px-1 py-3">Home team</th>
                            <th scope="col" className="px-3 py-3">Results</th>
                            <th scope="col" className="px-1 py-3">Away team</th>
                            <th scope="col" className="px-1 py-3">Updated At</th>
                            <th scope="col" className="px-1 py-3">Fetching fixture state</th>
                        </tr>

                    </thead>
                    <tbody>
                        {games.map((game: GameInterface, i) => (
                            <tr key={game.id}>
                                <td className="px-0.5 py-1">{i + 1}.</td>
                                <td className="px-0.5 py-1">{formatDate(game.date)}</td>
                                <td className="px-0.5 py-1">{game.home_team}</td>
                                <td className="px-0.5 py-1 flex gap-2 justify-center"><span>{game.ht_results ? game.ht_results : `-:-`}</span><span>{game.ht_results ? game.ft_results : `-:-`}</span></td>
                                <td className="px-0.5 py-1">{game.away_team}</td>
                                <td className="px-0.5 py-1">{formatDate(game.updated_at)}</td>
                                <td className="px-0.5 py-1">(Fetching state: {game.fetching_fixture_state || 'none'})</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            {games.length < 1 && 'List is empty!'}
        </div>
    )
}