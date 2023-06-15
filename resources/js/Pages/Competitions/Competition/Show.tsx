import { Link, usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "@/layout/DefaultLayout";

interface CountryInterface {
    id: string,
    name: string,
    slug: string,
    competitions: []

}

interface TeamInterface {
    id: string,
    name: string,
    slug: string,
}

const Show = () => {

    const { props } = usePage<any>();
    const { competition } = props
    console.log(competition)
    return (
        <DefaultLayout>
            <div>
                {competition.name}
                {competition.teams.map((team: TeamInterface) => (
                    <div className="ml-4" key={team.id}>
                        <Link href={`/teams/team/${team.id}`}>{team.name}</Link>
                    </div>
                ))}

            </div>
        </DefaultLayout>
    );
};

export default Show;
