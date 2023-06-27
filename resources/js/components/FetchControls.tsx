import TimeAgo from 'timeago-react';

type Props = {
    item: any,
    limitList: number[];
    setLimit: any;
}

function FetchControls({ item, limitList, setLimit }: Props) {
    return (
        <div className="flex gap-4">
            <div className="font-bold w-2/5">Last fetch date {item.last_fetch ? <TimeAgo datetime={item.last_fetch} locale='en' /> : 'never'}</div>
            <div className="w-3/5 flex gap-2">
                <label className="w-1/4 flex gap-1 items-center">
                    <span className="w-1/2">Stop at:</span>
                    <span className="w-1/2">
                        <select className="w-full" onChange={(e: any) => setLimit(e.target.value)}>
                            {limitList && limitList.map((number) =>
                                <option value={number} key={number}>{number}</option>
                            )}

                        </select>
                    </span>
                </label>
                <button className="flex w-3/4 justify-center rounded bg-primary p-3 font-medium text-gray">Fetch now!</button>
            </div>
        </div>
    )
}

export default FetchControls