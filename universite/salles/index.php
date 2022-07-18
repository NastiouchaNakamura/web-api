<?php
const RACINE = '../../';
require_once(RACINE . '../connect.php');
require_once(RACINE . 'modele.php');
require_once(RACINE . 'controleur.php');
CtlApiUniversiteSalles($_GET['id'] ?? '');
