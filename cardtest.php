<?php 

require_once( "fckeditor/fckeditor.php" );


if( $submit ?? false )
{
    
    $hand = fopen( "card.html" , "w+");
    
    fwrite( $hand, stripslashes( $_POST["newnews"] ?? '' ) );
    fclose( $hand );
}

?>
<A href='card.html' target='_blank'>view</a>
        <form method="post">
<?php

$oFCKeditor = new FCKeditor('newnews') ;

$oFCKeditor->Value      = file_get_contents( "card.html" );
$oFCKeditor->Height     = 700;
$oFCKeditor->Create() ;
?>
            <br>
            <input type="submit" name='submit' value="Submit">
        </form>