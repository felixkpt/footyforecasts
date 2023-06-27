
interface Props {
    items: any
}
const FixturesDetails = ({ items }: Props) => {

    return (
        <div className="ml-4.5 text-sm text-gray-200">
            {Object.keys(items).map((key, i) =>
                <div key={i}>
                    {items[key].name}, {items[key].counts} times.
                </div>
            )}
        </div>
    );
};

export default FixturesDetails;
