<?php

class BibleAdminRegister {
  function __construct() {
    add_action('admin_menu', array($this, 'adminPage'));
  }
  // Will appear on admin page settings menu
  function adminPage() {
    add_menu_page( "Bíblia Digital", "Bíblia Digital", "manage_options", "biblia-digital", array($this, "bibliaDigitalPage"), "dashicons-book", 100 );
  }

  function bibliaDigitalPage(){
    $imgPath = plugin_dir_url( 'biblia-digital' ) . "biblia-digital/build/bibliadigital.png";
  ?>
    <p><a href="https://estudobiblico.org"><img src="<?php echo $imgPath;?>" alt="estudobiblico.org" width="40%"></a>
  </p>
    <h3>Instruções:</h3>
    <p>
    Para instalar a Bíblia Digital no seu site basta criar uma página com o título que desejar e colocar o shortcode [biblia-digital] .
    </p>

    <p>
      <b>
      plugin gratuito desde que seja mantida a imagem Bíblia Digital com o link ativo estudobíblico.org
      </b>
    </p>
   
  <?php
  }
}

$bibleAdminRegister = new BibleAdminRegister();
?>