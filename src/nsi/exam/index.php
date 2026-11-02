<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Nsi\Exam;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode GET
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (!isset($_GET["id"])) {
            // Récupération des contrôles
            $exams = Exam::fetch_control_exams();
            
            // Réponse HTTP
            RestResponse::set(200, $exams);
        } else {
            // Lecture de l'identifiant
            if (str_ends_with($_GET["id"], "-c")) {
                // Correction
                $subject_id = substr($_GET["id"], 0, -2);
                $correction = true;
            } else {
                $subject_id = $_GET["id"];
                $correction = false;
            }

            // Vérification de l'identifiant
            $exams = Exam::fetch_control_exams();
            foreach (Exam::fetch_control_exams() as $exam) {
                if (str_starts_with($subject_id, $exam->id)) {
                    foreach ($exam->subjects as $subject) {
                        if ($subject->id == $subject_id) {
                            if (!$correction || $subject->has_correction) {
                                header("Content-type:application/pdf");
                                readfile(Exam::control_subject_pdf_path($exam->id, $subject->id, $correction));
                            } else {
                                RestResponse::set(404, UserError::new("Subject '$subject_id' does not have a correction"));
                            }
                            break;
                        }
                    }

                    RestResponse::set(404, UserError::new("Subject '$subject_id' does not exist for existing exam '$exam->id'"));
                    break;
                }
            }

            RestResponse::set(404, UserError::new("Subject '$subject_id' does not correspond to an existing exam"));
        }
        
    } else {
        RestResponse::set(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
    }
} catch (Throwable $throwable) {
    echo $throwable;
    RestResponse::set(500, ServerError::new($throwable));
}
