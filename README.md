- [🥝 KiwiQuill – A Markdown blog API](#-kiwiquill--a-markdown-blog-api)
  - [Posts](#posts)
    - [Reserved metadata attributes](#reserved-metadata-attributes)
  - [Developer stuff (scary)](#developer-stuff-scary)
    - [RSS Feed](#rss-feed)
    - [API routes](#api-routes)
      - [List all posts](#list-all-posts)
      - [Get a specific post by path](#get-a-specific-post-by-path)
      - [Get only the metadata about a post](#get-only-the-metadata-about-a-post)
      - [Get a specific post by its identifier](#get-a-specific-post-by-its-identifier)
      - [Searching for posts](#searching-for-posts)
    - [API pagination](#api-pagination)
    - [Adding new post container types](#adding-new-post-container-types)


---

# 🥝 KiwiQuill – A Markdown blog API

KiwiQuill is a simple, file-based blogging system. Posts are stored in Markdown and served via a REST API.

Get more info on [the official website](https://mia.kiwi/projects/kiwi.mia.0033)!


## Posts

Posts, or more broadly any text content, are stored in Markdown files (e.g. `cookie-recipe.md`) in your posts directory (`/posts/`, by default). They can be stored at the root of the directory, or within nested folders. The API path to the post is derived from the location of the file (e.g. `/posts/travel/japan 2025/tokyo.md` becomes `/travel/japan-2025/tokyo`).

Each posts can have metadata attached to it. The attributes are stored in a YAML frontmatter block at the top of the file:

```md
---
title: "KiwiQuill Intro"
date_published: 2025-07-08
---

# 🥝 KiwiQuill – A Markdown blog API

KiwiQuill is a simple, file-based blogging system. Posts are stored in Markdown and served via a REST API.

...
```

The content itself is edited externally, using your preferred editor. KiwiQuill does not feature any way to *EDIT* posts (yet).

Post files that start with a dot (".") and files that don't have the `.md` extension aren't shown in the API.

### Reserved metadata attributes

KiwiQuill has some special metadata attributes it can use to help filter your posts, those are:

- `id`: A unique identifier for the post, should be globally unique and should not contain whitespaces.
- `title`: The title of the post.
- `description`: A short description of the post.
- `author`: The name or email (or both) of the author (e.g. "Jane Doe \<jane.doe@example.com\>")
- `image`: The URI of an image associated with the post.
- `tags`: A list of tags that describe the post.
- `date_published`: The date the post was first published.
- `date_updated`: The date of the last revision.

## Developer stuff (scary)

### RSS Feed

An RSS feed is available at the endpoint `/feed`.

The raw endpoint contains a global channel that serves all the posts in the container, but you can append a tag name like such: `/feed/{tag}` to show a channel specific to that tag.

For example, `/feed/cooking` will show all the posts that have the "cooking" tag.

### API routes

The API version is `v1`.

#### List all posts

```
GET /api/v1/posts
```

Returns a [KAPIR](https://mia.kiwi/projects/kiwi.mia.kcs.0001) response, the data member of which is an array of objects with the following structure:

```json
{
    "raw": "The raw Markdown of the post (without the YAML frontmatter)",
    "metadata": {
        "key": "value (can be a number, a string, null, an array, or an object)"
    }
}
```

#### Get a specific post by path

```
GET /api/v1/posts/{path}
```

Returns a [KAPIR](https://mia.kiwi/projects/kiwi.mia.kcs.0001) response, the data member of which is an object with the following members:

```json
{
    "raw": "The raw Markdown of the post (without the YAML frontmatter)",
    "metadata": {
        "key": "value (can be a number, a string, null, an array, or an object)"
    }
}
```

If no post with that path is found, a `NOT_FOUND` error is returned:

```json
{
    "status": "error",
    "version": "25.1.0",
    "data": null,
    "message": "Post not found.",
    "error": {
        "code": "NOT_FOUND",
        "message": "Post with path 'hello' not found.",
        "errors": []
    },
    "meta": {
        "response_time": "2025-07-08 20:04:05+02:00"
    }
}
```

#### Get only the metadata about a post

```
GET /api/v1/posts/{path}/metadata
```

Returns a [KAPIR](https://mia.kiwi/projects/kiwi.mia.kcs.0001) response, the data member of which is an object representing the metadata attributes of the post.

If no post with that path is found, a `NOT_FOUND` error is returned:

```json
{
    "status": "error",
    "version": "25.1.0",
    "data": null,
    "message": "Post not found.",
    "error": {
        "code": "NOT_FOUND",
        "message": "Post with path 'world' not found.",
        "errors": []
    },
    "meta": {
        "response_time": "2025-07-08 20:04:05+02:00"
    }
}
```

#### Get a specific post by its identifier

If the desired post has an `id` metadata attribute, this endpoint can be used to fetch it directly.

```
GET /api/v1/posts/id/{id}
```

If no post with that identifier is found, a `NOT_FOUND` error is returned:

```json
{
    "status": "error",
    "version": "25.1.0",
    "data": null,
    "message": "Post not found.",
    "error": {
        "code": "NOT_FOUND",
        "message": "Post with ID '299fff' not found.",
        "errors": []
    },
    "meta": {
        "response_time": "2025-07-08 20:06:34+02:00"
    }
}
```

#### Searching for posts

You can use the `/api/v1/posts/search` endpoint to perform search operations and find posts based on your criteria.

Currently, the possible search parameters are:

| Name     | Format                    | Matches                                          |
| -------- | ------------------------- | ------------------------------------------------ |
| `tags`   | `?tags=tag1,tag2,tag3...` | All posts that have any of the tags in the list. |
| `title`  | `?title=...`              | All posts with that title.                       |
| `author` | `?author=...`             | All posts from that author                       |

These parameters can be combined to narrow your searches. For example:

```
GET /api/v1/posts/search?tags=recipe,blog&author=John
```

Returns all posts by John with the tags "recipe" or "blog".

### API pagination

You can specify pagination parameters to the API to select only a range of posts. The pagination parameters are `limit`, which defines how many posts you want to get, and `offset`, that specifies from which result index the limit starts. Effectively, this allows you to select the "page" number and the number of "rows".

The `limit` parameter must be an integer greater than zero, but lesser or equal to 100, and the `offset` must be a positive integer.

Paginated results include some additional data in the metadata member of the KAPIR response:

```json
{
    "status": "error",
    "version": "25.1.0",
    "data": [...],
    "message": "Posts retrieved successfully!",
    "error": null,
    "meta": {
        "pagination": {
            "offset": 0,
            "limit": 100,
            "total": 6,
            "has_more": false,
            "next_offset": null,
            "previous_offset": null
        },
        "response_time": "2025-07-09 16:28:20+02:00"
    }
}
```

The `has_more` member indicates if there are more posts on the next offset, the `total` member shows how many posts there are in total (not only in the response), and the `next_`/`previous_offset` members show the offset that corresponds to the previous or next "page".

### Adding new post container types

Developers can create their own post container types by implementing the `PostsContainerInterface`. By default, KiwiQuill is distributed with a file system container, but you are free to use your own.