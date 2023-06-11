import { usePage } from "@inertiajs/inertia-react";
import DefaultLayout from "../../layout/DefaultLayout";
import CompetitionsList from "./CompetitionsList";

interface CountryInterface {
    hashid: string,
    name: string,
    slug: string,
    competitions: []

}

const Index = () => {

    const { props } = usePage<any>();
    const { countries } = props

    return (
        <DefaultLayout>
            <div>
                {countries.map((country: CountryInterface) => (
                    <div key={country.hashid} className="flex flex-col w-full">
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 bg-white rounded-full inline-block"></div>
                            <div className="inline-block">{country.name}</div>
                        </div>
                        <div className="ml-14"><CompetitionsList competitions={country.competitions} /></div>
                    </div>
                ))}
            </div>
        </DefaultLayout>
    );
};

export default Index;
