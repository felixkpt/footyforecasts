import { Link } from "@inertiajs/inertia-react";

interface CompetitionsInterface {
    competitions: any

}

interface CompetitionInterface {
    uuid: string,
    name: string,
    slug: string,

}

const CompetitionsList = ({ competitions }: CompetitionsInterface) => {

    return (
            <div>
                {competitions.map((competition: CompetitionInterface) => (
                    <div key={competition.uuid}>
                        <Link href={`/competitions/${competition.uuid}`}>{competition.name}</Link>
                    </div>
                ))}
            </div>
    );
};

export default CompetitionsList;
