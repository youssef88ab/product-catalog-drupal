// themes/custom/drupal1/src/Entity/Book.php
<?php
namespace Drupal\drupal1\Entity;

class Book {
    private $title;
    private $author;
    private $published_date;

    public function __construct($title, $author, $published_date) {
        $this->title = $title;
        $this->author = $author;
        $this->published_date = $published_date;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getPublishedDate() {
        return $this->published_date;
    }
}
