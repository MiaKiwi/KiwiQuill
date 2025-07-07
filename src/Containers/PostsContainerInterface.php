<?php

namespace Miakiwi\Kiwiquill\Containers;

use DateTime;
use Miakiwi\Kiwiquill\Models\Post;



interface PostsContainerInterface
{
    /**
     * Get the posts from the container.
     * @return \Miakiwi\Kiwiquill\Models\Post[] The posts in the container.
     */
    public function getPosts(): array;



    /**
     * Get a post by its ID.
     * @param string $id The ID of the post to retrieve.
     * @return \Miakiwi\Kiwiquill\Models\Post|null The post with the specified ID, or null if not found.
     */
    public function getPostById(string $id): ?Post;



    /**
     * Get a post by its URL path.
     * @param string $path The URL path of the post to retrieve.
     * @return \Miakiwi\Kiwiquill\Models\Post|null The post with the specified path, or null if not found.
     */
    public function getPostByPath(string $path): ?Post;



    /**
     * Get posts by their tags.
     * @param string[] $tags The tags to filter posts by (combined with OR logic).
     * @return \Miakiwi\Kiwiquill\Models\Post[] The posts with the specified tags.
     */
    public function getPostsByTags(array $tags): array;



    /**
     * Get posts by their title.
     * @param string $title The title of the post to retrieve.
     * @return \Miakiwi\Kiwiquill\Models\Post[] The posts with the specified title.
     */
    public function getPostsByTitle(string $title): array;



    /**
     * Get posts by their author.
     * @param string $author The author of the post to retrieve.
     * @return \Miakiwi\Kiwiquill\Models\Post[] The posts with the specified author.
     */
    public function getPostsByAuthor(string $author): array;



    /**
     * Get posts by their publication date.
     * @param \DateTime $date The publication date of the post to retrieve.
     * @return \Miakiwi\Kiwiquill\Models\Post[] The posts with the specified publication date.
     */
    public function getPostsByPublicationDate(DateTime $date): array;



    /**
     * Get posts by their last update date.
     * @param \DateTime $date The last update date of the post to retrieve.
     * @return \Miakiwi\Kiwiquill\Models\Post[] The posts with the specified last update date.
     */
    public function getPostsByUpdateDate(DateTime $date): array;
}