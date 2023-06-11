interface Props {
    post: {
        uuid: string,
        title: string,
        content_short: string,
        content: string,
    }
}

const PostCard = ({ post }: Props) => {

    return (
        <div key={post.uuid} className="bg-white p-4">
            <a href="single.html" className="block mb-4">
                <img src="images/img_7_horizontal.jpg" alt="Image" className="w-full" />
            </a>
            <div className="mb-4">
                <h2 className="text-xl font-bold mb-2">
                    <a href="single.html">{post.title}</a>
                </h2>
                <div className="flex items-center text-sm hidden">
                    <figure className="mr-3">
                        <img src="images/person_1.jpg" alt="Image" className="w-8 h-8 rounded-full" />
                    </figure>
                    <span className="text-gray-600">By <a href="#">David Anderson</a></span>
                    <span className="mx-1">&nbsp;-&nbsp;</span>
                    <span className="text-gray-600">July 19, 2019</span>
                </div>
            </div>
            <p className="mb-4">
                {post.content_short}
            </p>
            <p>
                <a href="#" className="text-blue-500">Continue Reading</a>
            </p>
        </div>
    )
}

export default PostCard