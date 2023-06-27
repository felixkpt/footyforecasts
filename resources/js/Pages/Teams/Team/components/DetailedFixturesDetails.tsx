
interface Props {
    items: any
}
const FixturesDetails = ({ items }: Props) => {
    return (
        <div className="ml-4.5 text-sm text-gray-200">
            {items.action}
        </div>
    );
};

export default FixturesDetails;
