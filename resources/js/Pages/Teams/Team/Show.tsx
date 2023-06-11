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
    const { team } = props
    return (
        <DefaultLayout>
            <div>
                {team && <div className="ml-4">
                    {team.name}
                </div>
                }

            </div>
        </DefaultLayout>
    );
};

export default Show;
