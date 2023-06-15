<?php
/**
 * Plugin Configuration
 */
class BiblePluginConfig {
  public function __construct() {
    global $wpdb;
    $this->charset = $wpdb->get_charset_collate();
    $this->tablename = $wpdb->prefix . "bible";

    add_action('activate_plugin', array($this, 'onActivate'));
    add_action( 'deactivate_plugin', array($this, "onDeactivate"));
  }

  public function onDeactivate() {
    global $wpdb;
    $wpdb->query( $wpdb->prepare("DROP TABLE IF EXISTS $this->tablename") );
    $wpdb->flush();
  }

  function onActivate() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once plugin_dir_path( __FILE__ ) . '../config/sql.php';

    try {
      dbDelta($sqlData); 
    } catch (\Throwable $th) {
      throw $th;
    }

    // Insert books into database
    $this->insertTable($insertGn);
    $this->insertTable($insertEx);
    $this->insertTable($insertLv);
    $this->insertTable($insertNm);
    $this->insertTable($insertDt);
    $this->insertTable($insertJs);
    $this->insertTable($insertJs_A);
    $this->insertTable($insertJs_B);
    $this->insertTable($insertJz);
    $this->insertTable($insertJz_A);
    $this->insertTable($insertJz_B);
    $this->insertTable($insertRt);
    $this->insertTable($insert1Sm);
    $this->insertTable($insert2Sm);
    $this->insertTable($insert1Rs);
    $this->insertTable($insert2Rs);
    $this->insertTable($insert1Cr);
    $this->insertTable($insert2Cr);
    $this->insertTable($insertEd);
    $this->insertTable($insertNe);
    $this->insertTable($insertEt);
    $this->insertTable($insertJo);
    $this->insertTable($insertSl);
    $this->insertTable($insertPv);
    $this->insertTable($insertEc);
    $this->insertTable($insertCt);
    $this->insertTable($insertIs);
    $this->insertTable($insertJr);
    $this->insertTable($insertLm);
    $this->insertTable($insertEz);
    $this->insertTable($insertDn);
    $this->insertTable($insertOs);
    $this->insertTable($insertJl);
    $this->insertTable($insertAm);
    $this->insertTable($insertOb);
    $this->insertTable($insertJn);
    $this->insertTable($insertMq);
    $this->insertTable($insertNa);
    $this->insertTable($insertHc);
    $this->insertTable($insertSf);
    $this->insertTable($insertAg);
    $this->insertTable($insertZc);
    $this->insertTable($insertMl);
    $this->insertTable($insertMt);
    $this->insertTable($insertMc);
    $this->insertTable($insertLc);
    $this->insertTable($insertJoao);
    $this->insertTable($insertAt);
    $this->insertTable($insertRm);
    $this->insertTable($insert1Co);
    $this->insertTable($insert2Co);
    $this->insertTable($insertGl);
    $this->insertTable($insertEf);
    $this->insertTable($insertFp);
    $this->insertTable($insertCl);
    $this->insertTable($insert1Ts);
    $this->insertTable($insert2Ts);
    $this->insertTable($insert1Tm);
    $this->insertTable($insert2Tm);
    $this->insertTable($insertTt);
    $this->insertTable($insertFm);
    $this->insertTable($insertHb);
    $this->insertTable($insertTg);
    $this->insertTable($insert1Pe);
    $this->insertTable($insert2Pe);
    $this->insertTable($insert1Jo);
    $this->insertTable($insert2Jo);
    $this->insertTable($insert3Jo);
    $this->insertTable($insertJd);
    $this->insertTable($insertAp);             
  }

  private function insertTable($sql) {
    global $wpdb;
    try {
      $wpdb->query($sql);
      $wpdb->flush();
    } catch (\Throwable $th) {
      throw $th;
    }
  }
}

$biblePluginConfig = new BiblePluginConfig();
?>