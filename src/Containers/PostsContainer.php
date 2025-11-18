<?php

namespace Miakiwi\Kiwiquill\Containers;



abstract class PostsContainer implements PostsContainerInterface
{
    public function getPostsByMatchingPath(string $path): array
    {
        $matching_posts = [];

        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's path matches the specified path.
            if (str_starts_with($post?->getPath(), $path)) {
                $matching_posts[] = $post;
            }
        }

        // Return the array of posts that match the specified path.
        return $matching_posts;
    }



    public function getPostsByTags(array $tags): array
    {
        $matching_posts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post has any of the specified tags.
            foreach ($tags as $tag) {
                if (in_array($tag, $post?->metadata?->get('tags', []))) {
                    // If a matching tag is found, add the post to the results.
                    $matching_posts[] = $post;
                    break; // No need to check other tags for this post.
                }
            }
        }

        // Return the array of posts that match the specified tags.
        return $matching_posts;
    }



    public function getPostsByTitle(string $title): array
    {
        $matching_posts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's title matches the specified title.
            if (strtolower($post?->metadata?->getTitle() ?? '') === strtolower($title)) {
                // If a matching title is found, add the post to the results.
                $matching_posts[] = $post;
            }
        }

        // Return the array of posts that match the specified title.
        return $matching_posts;
    }



    public function getPostsByAuthor(string $author): array
    {
        $matching_posts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's author matches the specified author.
            if (strtolower($post?->metadata?->getAuthor() ?? '') === strtolower($author)) {
                // If a matching author is found, add the post to the results.
                $matching_posts[] = $post;
            }
        }

        // Return the array of posts that match the specified author.
        return $matching_posts;
    }



    public function getPostsByPublicationDate(\DateTime $date): array
    {
        $matching_posts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's publication date matches the specified date.
            $publicationDate = $post?->metadata?->getPublicationDate();

            if ($publicationDate && $publicationDate === $date->format('Y-m-d')) {
                // If a matching publication date is found, add the post to the results.
                $matching_posts[] = $post;
            }
        }

        // Return the array of posts that match the specified publication date.
        return $matching_posts;
    }



    public function getPostsByUpdateDate(\DateTime $date): array
    {
        $matching_posts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's update date matches the specified date.
            $updateDate = $post?->metadata?->getUpdateDate();

            if ($updateDate && $updateDate === $date->format('Y-m-d')) {
                // If a matching update date is found, add the post to the results.
                $matching_posts[] = $post;
            }
        }

        // Return the array of posts that match the specified update date.
        return $matching_posts;
    }



    public function getPostsByMetadata(string $key, string $value): array
    {
        $matching_posts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's metadata contains the specified key-value pair.
            if ($post?->metadata?->get($key) === $value) {
                // If a matching metadata is found, add the post to the results.
                $matching_posts[] = $post;
            }
        }



        // Return the array of posts that match the specified metadata.
        return $matching_posts;
    }



    public static function filterPosts(array $posts, ?array $tags = null, ?string $title = null, ?string $author = null, ?string $path = null): array
    {
        $matching_posts = $posts; // Start with all posts.



        // If a set of tags is provided, only keep the posts that match any of the tags.
        if ($tags) {
            $matching_posts = array_filter($matching_posts, function ($post) use ($tags) {
                foreach ($tags as $tag) {
                    if (in_array($tag, $post?->metadata?->get('tags', []))) {
                        return true; // Keep the post if it has any of the tags.
                    }
                }
                return false; // Exclude the post if it has none of the tags.
            });
        }



        // If a title is provided, only keep the posts that match the title.
        if ($title) {
            $matching_posts = array_filter($matching_posts, function ($post) use ($title) {
                return strtolower($post?->metadata?->getTitle() ?? '') === strtolower($title);
            });
        }



        // If an author is provided, only keep the posts that match the author.
        if ($author) {
            $matching_posts = array_filter($matching_posts, function ($post) use ($author) {
                return strtolower($post?->metadata?->getAuthor() ?? '') === strtolower($author);
            });
        }



        // If a path is provided, only keep the posts that match the path.
        if ($path) {
            $matching_posts = array_filter($matching_posts, function ($post) use ($path) {
                return str_starts_with($post?->getPath(), $path);
            });
        }



        // Return the filtered array of posts.
        return array_values($matching_posts); // Re-index the array to ensure it has sequential keys
    }



    public function getTags(): array
    {
        // Get all the posts in the container.
        $posts = $this->getPosts();



        // Initialize an array to hold unique tags.
        $tags = [];

        foreach ($posts as $post) {
            foreach ($post->metadata->getTags() as $tag) {
                // Add the tag to the array if it is not already present.
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        // Sort the tags alphabetically.
        sort($tags);



        // Return the array of unique tags.
        return $tags;
    }



    public function tagExists(string $tag): bool
    {
        // Get all the tags in the container.
        $tags = $this->getTags();



        return in_array($tag, $tags); // Check if the specified tag exists in the tags array.
    }
}