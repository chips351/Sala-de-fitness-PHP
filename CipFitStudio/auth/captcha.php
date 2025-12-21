<?php

class Captcha {
    
    private static function drawSquare($image, $x, $y, $size, $color, $black, $angle = 0) {
        $shapeSize = $size * 3;
        $temp = imagecreatetruecolor($shapeSize, $shapeSize);
        $transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);
        imagefill($temp, 0, 0, $transparent);
        imagesavealpha($temp, true);
        
        $center = $shapeSize / 2;
        imagefilledrectangle($temp, $center - $size, $center - $size, $center + $size, $center + $size, $color);
        imagerectangle($temp, $center - $size, $center - $size, $center + $size, $center + $size, $black);
        
        if ($angle != 0) {
            $rotated = imagerotate($temp, $angle, $transparent);
            $temp = $rotated;
        }
        
        $newWidth = imagesx($temp);
        $newHeight = imagesy($temp);
        imagecopy($image, $temp, $x - $newWidth / 2, $y - $newHeight / 2, 0, 0, $newWidth, $newHeight);
    }
    
    private static function drawTriangle($image, $x, $y, $size, $color, $black, $angle = 0) {
        $shapeSize = $size * 3;
        $temp = imagecreatetruecolor($shapeSize, $shapeSize);
        $transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);
        imagefill($temp, 0, 0, $transparent);
        imagesavealpha($temp, true);
        
        $center = $shapeSize / 2;
        $points = [
            $center, $center - $size,
            $center - $size, $center + $size,
            $center + $size, $center + $size
        ];
        imagefilledpolygon($temp, $points, 3, $color);
        imagepolygon($temp, $points, 3, $black);
        
        if ($angle != 0) {
            $rotated = imagerotate($temp, $angle, $transparent);
            $temp = $rotated;
        }
        
        $newWidth = imagesx($temp);
        $newHeight = imagesy($temp);
        imagecopy($image, $temp, $x - $newWidth / 2, $y - $newHeight / 2, 0, 0, $newWidth, $newHeight);
    }
    
    private static function drawCircle($image, $x, $y, $size, $color, $black) {
        imagefilledellipse($image, $x, $y, $size * 2, $size * 2, $color);
        imageellipse($image, $x, $y, $size * 2, $size * 2, $black);
    }
    
    private static function generateQuestions($counts) {
        $shapeNames = [
            'square' => 'patrate',
            'triangle' => 'triunghiuri',
            'circle' => 'cercuri'
        ];
        
        $colorNames = [
            'red' => 'rosie',
            'green' => 'verde',
            'blue' => 'albastra',
            'yellow' => 'galbena'
        ];
        
        $shapeTotal = [];
        foreach ($counts as $shape => $colors) {
            $shapeTotal[$shape] = array_sum($colors);
        }
        
        // Intrebare 1: Cate $shape sunt?
        $shapes = array_keys($shapeTotal);
        $randomShape1 = $shapes[array_rand($shapes)];
        $q1 = "Cate {$shapeNames[$randomShape1]} sunt?";
        $a1 = $shapeTotal[$randomShape1];
        
        // Intrebare 2: Cate $shape de culoare $color sunt?
        // Selectare combinatie random cu valoare > 0
        $availableCombinations = [];
        foreach ($counts as $shape => $colors) {
            foreach ($colors as $color => $count) {
                if ($count > 0) {
                    $availableCombinations[] = ['shape' => $shape, 'color' => $color, 'count' => $count];
                }
            }
        }
        
        $randomCombo = $availableCombinations[array_rand($availableCombinations)];
        $q2 = "Cate {$shapeNames[$randomCombo['shape']]} de culoare {$colorNames[$randomCombo['color']]} sunt?";
        $a2 = $randomCombo['count'];
        
        $_SESSION['captcha_questions'] = ['q1' => $q1, 'q2' => $q2];
        $_SESSION['captcha_answers'] = ['q1' => $a1, 'q2' => $a2];
    }
    
    public static function generateImage() {
        $width = 400;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        $colors = [
            'red' => imagecolorallocate($image, 255, 0, 0),
            'green' => imagecolorallocate($image, 0, 200, 0),
            'blue' => imagecolorallocate($image, 0, 0, 255),
            'yellow' => imagecolorallocate($image, 255, 255, 0)
        ];
        $colorKeys = array_keys($colors);
        $maxSize = 40;
        $minSize = 20;
        $shapes = ['square', 'triangle', 'circle'];
        $positions = [];
        $counts = [];
        $totalToDraw = rand(7, 11);
        $drawn = 0;
        for ($i = 0; $i < $totalToDraw; $i++) {
            $shape = $shapes[array_rand($shapes)];
            $colorKey = $colorKeys[array_rand($colorKeys)];
            $color = $colors[$colorKey];
            $size = rand($minSize, $maxSize);
            $angle = rand(-90, 90);
            $margin = ceil($size * 1.5);
            $minDistance = $size * 2; //distana minima intre forme 
            $attempts = 0;
            $maxAttempts = 100;
            do {
                $x = rand($margin, $width - $margin);
                $y = rand($margin, $height - $margin);
                $valid = true;
                foreach ($positions as $pos) {
                    $distance = sqrt(pow($x - $pos['x'], 2) + pow($y - $pos['y'], 2));
                    if ($distance < $minDistance) {
                        $valid = false;
                        break;
                    }
                }
                $attempts++;
            } while (!$valid && $attempts < $maxAttempts);
            if ($attempts >= $maxAttempts) {
                $i--;
                continue;
            }
            $positions[] = ['x' => $x, 'y' => $y];
            if (!isset($counts[$shape])) {
                $counts[$shape] = [];
            }
            if (!isset($counts[$shape][$colorKey])) {
                $counts[$shape][$colorKey] = 0;
            }
            $counts[$shape][$colorKey]++;
            if ($shape === 'square') {
                self::drawSquare($image, $x, $y, $size, $color, $black, $angle);
            } elseif ($shape === 'triangle') {
                self::drawTriangle($image, $x, $y, $size, $color, $black, $angle);
            } else {
                self::drawCircle($image, $x, $y, $size, $color, $black);
            }
            $drawn++;
        }
        // Stergere intrebari vechi
        unset($_SESSION['captcha_questions']);
        unset($_SESSION['captcha_answers']);
        // Generare intrebari noi
        self::generateQuestions($counts);
        
        // Forteaza salvarea sesiunii inainte de a trimite imaginea
        session_write_close();
        header('Content-Type: image/png');
        imagepng($image);
    }
    
    public static function verify($answer1, $answer2) {
        if (!isset($_SESSION['captcha_answers'])) {
            return false;
        }
        
        $correctAnswers = $_SESSION['captcha_answers'];
        
        $isCorrect = (
            (int)$answer1 === (int)$correctAnswers['q1'] && 
            (int)$answer2 === (int)$correctAnswers['q2']
        );
        
        if ($isCorrect) {
            unset($_SESSION['captcha_questions']);
            unset($_SESSION['captcha_answers']);
        }
        
        return $isCorrect;
    }
    
    public static function getQuestions() {
        return $_SESSION['captcha_questions'] ?? ['q1' => '', 'q2' => ''];
    }
}
?>
