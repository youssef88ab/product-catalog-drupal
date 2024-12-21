// themes/custom/drupal1/template.php
<?php
use Drupal\drupal1\Entity\Book;

function custom_theme_function() {
    // Création d'un objet Book
    $book = new Book('Introduction to Drupal', 'John Doe', '2020-01-01');

    // Exemple d'affichage des détails du livre
    $output = '<h2>Book Details</h2>';
    $output .= '<p>Title: ' . $book->getTitle() . '</p>';
    $output .= '<p>Author: ' . $book->getAuthor() . '</p>';
    $output .= '<p>Published Date: ' . $book->getPublishedDate() . '</p>';

    return $output;
}
