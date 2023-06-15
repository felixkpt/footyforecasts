import DefaultLayout from "@/layout/DefaultLayout";
import { usePage } from "@inertiajs/inertia-react";
import PostCard from "./PostCard";

interface Post {
    id: string,
    title: string,
    content_short: string,
    content: string,
}

const Index = () => {

    const { props } = usePage<any>();
    const { posts } = props

    return (
        <DefaultLayout>
            <div className="container mx-auto">

                <div className="flex flex-wrap mb-4">
                    <div className="w-full sm:w-1/2">
                        <h2 className="text-xl font-bold mb-2">Politics</h2>
                    </div>
                    <div className="w-full sm:w-1/2 text-right">
                        <a href="category.html" className="text-blue-500">View All</a>
                    </div>
                </div>

                <div className="flex flex-wrap gap-1">
                    {posts && posts.map((post: Post) => (
                        <div key={post.id} className="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/4 mb-4">
                            <PostCard post={post} />
                        </div>
                    ))}
                </div>
            </div>
        </DefaultLayout>
    );
};

export default Index;
