<?php
namespace Tests\Continuum;

use CodeIgniter\Test\CIUnitTestCase;
use Continuum\Applications\Domain\ConsentPreview;

final class ConsentPreviewTest extends CIUnitTestCase
{
    private array $claims;
    protected function setUp(): void
    {
        parent::setUp();
        $this->claims = [
            ['id'=>1,'signal'=>'reasoning_judgement','label'=>'supported','claim_text'=>'A','source_excerpt'=>'src','requirement'=>'SQL'],
            ['id'=>2,'signal'=>'delivery_reliability','label'=>'stated','claim_text'=>'B','source_excerpt'=>null,'requirement'=>null],
            ['id'=>3,'signal'=>'initiative_ownership','label'=>'stated','claim_text'=>'private','source_excerpt'=>null,'requirement'=>null],
        ];
    }
    public function testUnsharedClaimExcluded(): void
    {
        $cp = new ConsentPreview();
        $s = $cp->summary($this->claims, [1,2]);
        $this->assertCount(2, $s);
        $this->assertNotContains(3, array_column($s, 'id'));
    }
    public function testEmployerViewEqualsCandidatePreview(): void
    {
        $cp = new ConsentPreview();
        $candidate = $cp->summary($this->claims, [1,2]);
        $hash = $cp->previewHash($candidate, 42);
        $employer = $cp->summary($this->claims, [1,2]);
        $this->assertTrue($cp->matchesEmployerView($employer, 42, $hash));
    }
    public function testTamperOrWrongRoleVersionBreaksHash(): void
    {
        $cp = new ConsentPreview();
        $candidate = $cp->summary($this->claims, [1,2]);
        $hash = $cp->previewHash($candidate, 42);
        $this->assertFalse($cp->matchesEmployerView($cp->summary($this->claims, [1,2,3]), 42, $hash));
        $this->assertFalse($cp->matchesEmployerView($candidate, 99, $hash));
    }
}
