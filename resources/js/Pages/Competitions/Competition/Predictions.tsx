import { Link, usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "@/layout/DefaultLayout";
import request from "@/utils/request";
import { useEffect, useState } from "react";
import Nav from "./components/Nav";

interface TeamInterface {
    id: string;
    name: string;
    slug: string;
}
interface CompetitionInterface {
    id: string;
    name: string;
    slug: string;
    teams: [TeamInterface];
    status: string;
}

const Prediction = () => {
    const { props } = usePage<any>();

    const [competition, setCompetition] = useState<CompetitionInterface>()

    useEffect(() => {
        let { competition: compe } = props

        setCompetition(compe)

    }, [props.competition])

    return (
        <DefaultLayout>
            <div>
                <Nav title="Predictions" competition={competition} setCompetition={setCompetition}/>
                <div>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magnam ad error quia similique quis praesentium consectetur, optio dolorum impedit corporis numquam assumenda tenetur aperiam. Natus minima harum consequatur itaque eveniet.
                </div>

            </div>
        </DefaultLayout>
    );
};

export default Prediction;
