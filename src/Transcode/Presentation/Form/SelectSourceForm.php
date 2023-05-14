<?php

declare(strict_types=1);

namespace App\Transcode\Presentation\Form;

use App\Transcode\Application\Service\TranscodeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class SelectSourceForm extends AbstractType
{
    public function __construct(
        private readonly TranscodeService $transcodeService,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', ChoiceType::class, [
            'label' => false,
            'choice_label' => 'fileName',
            'choice_value' => 'filePath',
            'choices' => $this->transcodeService->listAvailableVideos(),
        ]);

//        $builder->add('file', FileType::class, [
//            'choices' => $this->getFiles($_ENV['VIDEO_PATH']),
//        ]);
    }

    private function getFiles($directory): array
    {
        $files = [];

        // Open the directory
        if ($handle = opendir($directory)) {
            // Read all the files in the directory
            while (false !== ($entry = readdir($handle))) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                // Add the file path to the list of files
                $files[] = $directory . '/' . $entry;
            }

            closedir($handle);
        }

        return $files;
    }
}
