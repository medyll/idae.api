<?
    include_once($_SERVER['CONF_INC']);

    /* $Demo = new Demo();
	 $Demo->launch_demo();*/

    $a = new DateTime('now');
    $b = roundToNextXMin($a,5);

    var_dump($b);
    $BIN         = new Bin();
    $affect      = $BIN->test_livreur_affect(59831);
    $affect_free = $BIN->test_livreur_affect_free(59831);
    $affect_wait = $BIN->test_livreur_affect_wait(59831);

    var_dump($affect);
    var_dump($affect_free);
    var_dump($affect_wait);

?>
<div style="height:100%;">
    commande ok
</div>