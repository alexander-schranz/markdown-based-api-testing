<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\AbstractApiTest;

class ExampleControllerTest extends AbstractApiTest
{
    /**
     * @dataProvider provideData()
     */
    public function testFixtures(\SplFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return \Generator<\SplFileInfo>
     */
    public function provideData(): \Generator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/fixtures');
    }
}
