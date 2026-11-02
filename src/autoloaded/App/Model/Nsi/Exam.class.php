<?php

namespace App\Model\Nsi;

use DateTime;
use RuntimeException;

class Exam {
    public string $id;
    public string $title;
    public int $theme;
    public DateTime $update_time;
    public array $skills;
    public array $subjects;

    public static function fetch_control_exams(): array {
        if (!getenv("CONTROL_SUBJECTS_ROOT")) {
            throw new RuntimeException("CONTROL_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("CONTROL_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::fetch_exams($root);
    }

    public static function fetch_bac_exams(): array {
        if (!getenv("BAC_SUBJECTS_ROOT")) {
            throw new RuntimeException("BAC_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("BAC_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::fetch_exams($root);
    }

    private static function fetch_exams(string $root): array {
        if (!file_exists($root)) {
            throw new RuntimeException("File or directory '$root' does not exist");
        }

        if (!is_dir($root)) {
            throw new RuntimeException("'$root' is not a directory");
        }

        $exams = [];
        foreach (scandir($root) as $file_name) {
            $subpath = $root . "/" . $file_name;
            if (!str_starts_with($file_name, ".") && is_dir($subpath)) {
                $exam_id = $file_name;
                if (file_exists($root . "/" . $exam_id . "/exam.json")) {
                    array_push($exams, Exam::fetch_exam($root, $exam_id));
                }
            }
        }

        return $exams;
    }

    public static function control_exam_exists(string $id): bool {
        if (!getenv("CONTROL_SUBJECTS_ROOT")) {
            throw new RuntimeException("CONTROL_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("CONTROL_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::exam_exists($root, $id);
    }

    public static function bac_exam_exists(string $id): bool {
        if (!getenv("BAC_SUBJECTS_ROOT")) {
            throw new RuntimeException("BAC_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("BAC_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::exam_exists($root, $id);
    }

    private static function exam_exists(string $root, string $id): bool {
        if (!file_exists($root)) {
            throw new RuntimeException("File or directory '$root' does not exist");
        }

        if (!is_dir($root)) {
            throw new RuntimeException("'$root' is not a directory");
        }

        return file_exists($root . "/" . $id) && is_dir($root . "/" . $id);
    }

    public static function fetch_control_exam(string $id): Exam {
        if (!getenv("CONTROL_SUBJECTS_ROOT")) {
            throw new RuntimeException("CONTROL_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("CONTROL_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::fetch_exam($root, $id);
    }

    public static function fetch_bac_exam(string $id): Exam {
        if (!getenv("BAC_SUBJECTS_ROOT")) {
            throw new RuntimeException("BAC_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("BAC_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::fetch_exam($root, $id);
    }

    private static function fetch_exam(string $root, string $id): Exam {
        if (!file_exists($root)) {
            throw new RuntimeException("File or directory '$root' does not exist");
        }

        if (!is_dir($root)) {
            throw new RuntimeException("'$root' is not a directory");
        }

        $exam_dir = $root . "/" . $id;
        $exam_json_path = $exam_dir . "/exam.json";
        if (!file_exists($exam_json_path)) {
            throw new RuntimeException("'$root' has no exam JSON file");
        }

        $update_time = new DateTime(date("F d Y H:i:s.", filemtime($exam_dir)));
        $exam_json = json_decode(file_get_contents($exam_json_path));
        $exam = new Exam();
        $exam->id = $id;
        $exam->title = $exam_json->title;
        $exam->theme = $exam_json->theme;
        $exam->update_time = $update_time;
        $exam->skills = $exam_json->skills;
        $exam->subjects = [];

        foreach (scandir($exam_dir) as $subject_file) {
            if (str_ends_with($subject_file, ".pdf") && !str_ends_with($subject_file, "-c.pdf")) {
                $subject = new Subject();
                $subject->id = substr($subject_file, 0, -4);
                $subject->has_correction = file_exists($exam_dir . "/" . $subject->id . "-c.pdf");

                array_push($exam->subjects, $subject);
            }
        }

        return $exam;
    }

    public static function control_subject_pdf_path(string $exam_id, string $subject_id, bool $correction): string {
        if (!getenv("CONTROL_SUBJECTS_ROOT")) {
            throw new RuntimeException("CONTROL_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("CONTROL_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::subject_pdf_path($root, $exam_id, $subject_id, $correction);
    }

    public static function bac_subject_pdf_path(string $exam_id, string $subject_id, bool $correction): string {
        if (!getenv("BAC_SUBJECTS_ROOT")) {
            throw new RuntimeException("BAC_SUBJECTS_ROOT environment variable is not set");
        }

        $root = getenv("BAC_SUBJECTS_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        return Exam::subject_pdf_path($root, $exam_id, $subject_id, $correction);
    }

    private static function subject_pdf_path(string $root, string $exam_id, string $subject_id, bool $correction): string {
        $exam = Exam::fetch_exam($root, $exam_id);

        foreach ($exam->subjects as $subject) {
            if ($subject->id == $subject_id) {
                return $root . "/" . $exam_id . "/" . $subject_id . ($correction ? "-c.pdf" : ".pdf");
            }
        }

        throw new RuntimeException("Subject '$subject_id' does not exist for existing exam '$exam_id'");
    }
}
