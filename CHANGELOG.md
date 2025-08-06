# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [STVP](https://mia.kiwi/projects/stvp).

## [Unreleased]

### Added

- Post 'model' with methods for parsing markdown files and extracting metadata from the YAML front matter
- Slugificator
- Post metadata class
- Post container interface
- Default post container type 'filesystem'
- README file
- A warning when a post ID contains spaces
- Logging level and config loading in the app bootstrap
- APIv1 routes
- Posts controller
- Tags controller
- RSS feed endpoint
- Route to get metadata of all posts
- Method `getPostsByMatchingPath` to `PostsContainerInterface` to find posts with similar paths, effectively allowing posts to be in hierarchical "folders"
- `__toString` method to `Post` model
- `getPostsByMetadata` method to `PostsContainerInterface` to get posts based on their metadata
- `filterPosts` method to `PostsContainerInterface` to filter a set of posts based on a number of criteria
- `PostsContainer` abstract class to hold common methods not reliant on the storage implementation

### Changed

- Moved `PostsContainer` methods of `FSPostsContainer`
