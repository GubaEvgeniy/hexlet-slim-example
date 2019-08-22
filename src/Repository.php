<?php


namespace App;


final class Repository
{
    public function __construct()
    {
        session_start();
        if (!array_key_exists('posts', $_SESSION)) {
            $_SESSION['posts'] = [];
        }
    }

    public function all()
    {
        return array_values($_SESSION);
    }

    public function find(int $id)
    {
        return $_SESSION[$id];
    }

    public function save(array $item)
    {
        if (empty($item['title']) || $item['paid'] == '') {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }
        $item['id'] = uniqid();
        $_SESSION[$item['id']] = $item;
    }

    public function savePost(array $item)
    {
        if (empty($item['name']) || empty($item['body'])) {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }
        if (!isset($item['id'])) {
            $item['id'] = uniqid();
        }
        $_SESSION['posts'][$item['id']] = $item;

        return $item['id'];
    }
    public function destroyPosts(string $id)
    {
        unset($_SESSION['posts'][$id]);
    }
    public function findPosts($id)
    {
        return $_SESSION['posts'][$id];
    }
    public function allPosts()
    {
        return array_values($_SESSION['posts']);
    }
}