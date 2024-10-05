<?php

namespace Mediashare\Marathon\Service;

class EditorService {
    public function __construct(
        private readonly ConfigService $configService,
    ) { }

    public function getMessageFromEditor(string|null $placeholder = null): string|false {
        $editor = $this->configService->getConfig()->getEditor();
        $tempFile = tempnam(sys_get_temp_dir(), uniqid('marathon_', true));

        if ($placeholder !== null):
            file_put_contents($tempFile, $placeholder);
        endif;

        $descriptorspec = array(
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        );

        $process = proc_open("$editor $tempFile", $descriptorspec, $pipes);

        if (is_resource($process)) {
            proc_close($process);
        }

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }
}