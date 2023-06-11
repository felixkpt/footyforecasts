import { Link, usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "../../../layout/DefaultLayout";

interface CountryInterface {
    uuid: string,
    name: string,
    slug: string,
    competitions: []

}

interface TeamInterface {
    uuid: string,
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
                    <div className="ml-4" key={team.uuid}>
                        <Link href={`/teams/${team.uuid}`}>{team.name}</Link>
                    </div>
                ))}

            </div>
        </DefaultLayout>
    );
};

export default Show;
