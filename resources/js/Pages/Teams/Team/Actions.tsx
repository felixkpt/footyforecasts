import { usePage } from "@inertiajs/inertia-react";
import { useState } from "react";
import DefaultLayout from "@/layout/DefaultLayout";
import request from '@/utils/request'

const Create = () => {

    const { props } = usePage<any>();
    const { team } = props
 
    const [status, setStatus] = useState('fixtures')

    const handleSubmit = (e: any) => {
        e.preventDefault()
        request.post(`/teams/team/${team.id}/actions`, { status }).then((data: any) => {
            alert(`Post ${data.id} saved!`)
        })
    }

    return (
        <DefaultLayout>
            <div>
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <div className="font-medium flex gap-3 justify-between cursor-default"><h3 className="text-black dark:text-white">Team actions</h3><span className="text-gray-300">({team.name})</span></div>
                    </div>
                    <form action="#" onSubmit={handleSubmit}>
                        <div className="p-6.5">
                            <div className="mb-4.5 form-group">
                                <label className="mb-2.5 block text-black dark:text-white">Action</label>
                                <select className="appearance-none w-full py-1 px-2 bg-white" name="status" value={status} onChange={(e) => setStatus(e.target.value)}>
                                    <option value="fixtures">Fetch Fixtures</option>
                                    <option value="detailedFixtures">Fetch Detailed Fixtures</option>
                                    <option value="results">Fetch Results</option>
                                    <option value="changeStatus">Team Disable</option>
                                </select>
                            </div>
                            <button className="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </DefaultLayout>
    );
};

export default Create;
