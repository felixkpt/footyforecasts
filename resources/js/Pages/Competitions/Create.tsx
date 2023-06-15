import { useState } from "react";
import DefaultLayout from "../../layout/DefaultLayout";
import axios from "axios";

const Create = () => {

    const [source, setSource] = useState('')

    const handleSubmit = (e: any) => {
        e.preventDefault()
        axios.post('/competitions', { source }).then(resp => {
            const { data } = resp.data
            alert(`Competition saved, ${data.length} teams were also saved!`)
        })
    }

    return (
        <DefaultLayout>
            <div>
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Add a new competition</h3>
                    </div>
                    <form action="#" onSubmit={handleSubmit}>
                        <div className="p-6.5">
                            <div className="mb-4.5">
                                <label className="mb-2.5 block text-black dark:text-white">Competition url</label>
                                <input value={source} onChange={(e) => setSource(e.target.value)} name="url" type="text" placeholder="Enter Competition url" className="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                            </div>
                            <button className="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray">Fetch!</button>
                        </div>
                    </form>
                </div>
            </div>
        </DefaultLayout>
    );
};

export default Create;
