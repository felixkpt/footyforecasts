
interface Props {
    items: any,
    numbered?: any
}
const FixturesDetails = ({ items, numbered }: Props) => {

    return (
        <div className="ml-4.5 text-sm text-gray-200">
            {Object.keys(items).map((key, i) =>
                <div key={i}>
                    {numbered && `${i + 1}. `} {items[key].name}, {items[key].counts} times.
                </div>
            )}
        </div>
    );
};

export default FixturesDetails;
