import { Link } from "@inertiajs/inertia-react";

interface CompetitionsInterface {
    competitions: any

}

interface CompetitionInterface {
    id: string,
    name: string,
    slug: string,

}

const CompetitionsList = ({ competitions }: CompetitionsInterface) => {

    return (
            <div>
                {competitions.map((competition: CompetitionInterface) => (
                    <div key={competition.id}>
                        <Link href={`/competitions/${competition.id}`}>{competition.name}</Link>
                    </div>
                ))}
            </div>
    );
};

export default CompetitionsList;
