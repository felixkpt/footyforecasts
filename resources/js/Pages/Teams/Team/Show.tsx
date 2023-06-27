import { Link, usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "../../../layout/DefaultLayout";
import Nav from "./components/Nav";
import { useEffect, useState } from "react";

interface GameInterface {
    id: string;
    competition_abbreviation: string;
    slug: string;
    last_fetch: string
    action: string;
    fetching_fixture_state: number;
}

interface TeamInterface {
    id: string;
    name: string;
    slug: string;
    games: GameInterface[]
}
const Show = () => {

    const { props } = usePage<any>();

    const [team, setTeam] = useState<TeamInterface>()

    useEffect(() => {
        let { team: tmp } = props

        setTeam(tmp)

    }, [props.team])

    return (
        <DefaultLayout>
            <div>
                <Nav title="Games" team={team} setTeam={setTeam} />
                {team?.games &&
                    <div>
                        {team.games.map((game) =>
                            <div key={game.id}>
                                aaaaa
                            </div>
                        )}
                    </div>
                }
            </div>
        </DefaultLayout>
    );
};

export default Show;
