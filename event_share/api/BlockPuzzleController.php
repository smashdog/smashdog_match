<?php
use  \Fastknife\Service\BlockPuzzleCaptchaService;
class BlockPuzzleController
{
    public function get()
    {
        $config = (require './config.php')['captcha'];
        $service = new BlockPuzzleCaptchaService($config);
        $data = $service->get();
        echo json_encode([
            'error' => false,
            'repCode' => '0000',
            'repData' => $data,
            'repMsg' => null,
            'success' => true,
        ]);
    }

    /**
     * 一次验证
     */
    public function check()
    {
        $config = (require './config.php')['captcha'];
        $service = new BlockPuzzleCaptchaService($config);
        $data = json_decode(file_get_contents('php://input'), true);
        $msg = null;
        $error = false;
        $repCode = '0000';
        try {
            $service->check($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $error = true;
            $repCode = '6111';
        }
        echo json_encode([
            'error' => $error,
            'repCode' => $repCode,
            'repData' => null,
            'repMsg' => $msg,
            'success' => !$error,
        ]);
    }

    /**
     * 二次验证
     */
    public function verification()
    {
        $config = (require './config.php')['captcha'];
        $service = new BlockPuzzleCaptchaService($config);
        $data = json_decode(file_get_contents('php://input'), true);
        $msg = null;
        $error = false;
        $repCode = '0000';

        try {
            if (isset($data['captchaVerification'])) {
                $service->verificationByEncryptCode($data['captchaVerification']);
            } else if (isset($data['token']) && isset($data['pointJson'])) {
                $service->verification($data['token'], $data['pointJson']);
            } else {
                throw new \Exception('参数错误！');
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $error = true;
            $repCode = '6111';
        }
        echo json_encode([
            'error' => $error,
            'repCode' => $repCode,
            'repData' => null,
            'repMsg' => $msg,
            'success' => !$error,
        ]);
    }
}
