<?php
namespace Models;

require_once __DIR__ . '/../classes/DatabaseGeneral.php'; // เพิ่มบรรทัดนี้

use App\DatabaseGeneral;
use PDO;

class ReportRepair
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseGeneral();
    }

    public function insert($data)
    {
        $sql = "INSERT INTO report_repair (
            AddDate, AddLocation,
            doorCount, doorDamage, windowCount, windowDamage, tablestCount, tablestDamage,
            chairstCount, chairstDamage, tabletaCount, tabletaDamage, chairtaCount, chairtaDamage,
            other1Details, other1Count, other1Damage,
            tvCount, tvDamage, audioCount, audioDamage, hdmiCount, hdmiDamage, projectorCount, projectorDamage,
            other2Details, other2Count, other2Damage,
            fanCount, fanDamage, lightCount, lightDamage, airCount, airDamage,
            swCount, swDamage, swfanCount, swfanDamage, plugCount, plugDamage,
            other3Details, other3Count, other3Damage,
            teach_id, term, pee, status
        ) VALUES (
            :AddDate, :AddLocation,
            :doorCount, :doorDamage, :windowCount, :windowDamage, :tablestCount, :tablestDamage,
            :chairstCount, :chairstDamage, :tabletaCount, :tabletaDamage, :chairtaCount, :chairtaDamage,
            :other1Details, :other1Count, :other1Damage,
            :tvCount, :tvDamage, :audioCount, :audioDamage, :hdmiCount, :hdmiDamage, :projectorCount, :projectorDamage,
            :other2Details, :other2Count, :other2Damage,
            :fanCount, :fanDamage, :lightCount, :lightDamage, :airCount, :airDamage,
            :swCount, :swDamage, :swfanCount, :swfanDamage, :plugCount, :plugDamage,
            :other3Details, :other3Count, :other3Damage,
            :teach_id, :term, :pee, :status
        )";
        $stmt = $this->db->query($sql, $data);
        return $stmt->rowCount() > 0;
    }

    public function getByTeacher($teach_id)
    {
        $sql = "SELECT * FROM report_repair WHERE teach_id = :teach_id ORDER BY AddDate DESC, id DESC";
        $stmt = $this->db->query($sql, ['teach_id' => $teach_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM report_repair WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id, $teach_id)
    {
        $sql = "DELETE FROM report_repair WHERE id = :id AND teach_id = :teach_id";
        $stmt = $this->db->query($sql, ['id' => $id, 'teach_id' => $teach_id]);
        return $stmt->rowCount() > 0;
    }

    public function deleteById($id)
    {
        $sql = "DELETE FROM report_repair WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function update($data, $teacher_id = null)
    {
        // ตรวจสอบว่าเป็นของครูที่ถูกต้องหรือไม่ (ถ้าระบุ teacher_id)
        $whereClause = "id = :id";
        $params = ['id' => $data['id']];
        
        if ($teacher_id) {
            $whereClause .= " AND teach_id = :teach_id";
            $params['teach_id'] = $teacher_id;
        }

        $sql = "UPDATE report_repair SET 
            AddDate = :AddDate, AddLocation = :AddLocation,
            doorCount = :doorCount, doorDamage = :doorDamage, 
            windowCount = :windowCount, windowDamage = :windowDamage,
            tablestCount = :tablestCount, tablestDamage = :tablestDamage,
            chairstCount = :chairstCount, chairstDamage = :chairstDamage,
            tabletaCount = :tabletaCount, tabletaDamage = :tabletaDamage,
            chairtaCount = :chairtaCount, chairtaDamage = :chairtaDamage,
            other1Details = :other1Details, other1Count = :other1Count, other1Damage = :other1Damage,
            tvCount = :tvCount, tvDamage = :tvDamage,
            audioCount = :audioCount, audioDamage = :audioDamage,
            hdmiCount = :hdmiCount, hdmiDamage = :hdmiDamage,
            projectorCount = :projectorCount, projectorDamage = :projectorDamage,
            other2Details = :other2Details, other2Count = :other2Count, other2Damage = :other2Damage,
            fanCount = :fanCount, fanDamage = :fanDamage,
            lightCount = :lightCount, lightDamage = :lightDamage,
            airCount = :airCount, airDamage = :airDamage,
            swCount = :swCount, swDamage = :swDamage,
            swfanCount = :swfanCount, swfanDamage = :swfanDamage,
            plugCount = :plugCount, plugDamage = :plugDamage,
            other3Details = :other3Details, other3Count = :other3Count, other3Damage = :other3Damage
            WHERE $whereClause";

        // เตรียมข้อมูลสำหรับ bind
        $updateParams = array_merge($params, [
            'AddDate' => $data['AddDate'],
            'AddLocation' => $data['AddLocation'],
            'doorCount' => $data['doorCount'],
            'doorDamage' => $data['doorDamage'],
            'windowCount' => $data['windowCount'],
            'windowDamage' => $data['windowDamage'],
            'tablestCount' => $data['tablestCount'],
            'tablestDamage' => $data['tablestDamage'],
            'chairstCount' => $data['chairstCount'],
            'chairstDamage' => $data['chairstDamage'],
            'tabletaCount' => $data['tabletaCount'],
            'tabletaDamage' => $data['tabletaDamage'],
            'chairtaCount' => $data['chairtaCount'],
            'chairtaDamage' => $data['chairtaDamage'],
            'other1Details' => $data['other1Details'],
            'other1Count' => $data['other1Count'],
            'other1Damage' => $data['other1Damage'],
            'tvCount' => $data['tvCount'],
            'tvDamage' => $data['tvDamage'],
            'audioCount' => $data['audioCount'],
            'audioDamage' => $data['audioDamage'],
            'hdmiCount' => $data['hdmiCount'],
            'hdmiDamage' => $data['hdmiDamage'],
            'projectorCount' => $data['projectorCount'],
            'projectorDamage' => $data['projectorDamage'],
            'other2Details' => $data['other2Details'],
            'other2Count' => $data['other2Count'],
            'other2Damage' => $data['other2Damage'],
            'fanCount' => $data['fanCount'],
            'fanDamage' => $data['fanDamage'],
            'lightCount' => $data['lightCount'],
            'lightDamage' => $data['lightDamage'],
            'airCount' => $data['airCount'],
            'airDamage' => $data['airDamage'],
            'swCount' => $data['swCount'],
            'swDamage' => $data['swDamage'],
            'swfanCount' => $data['swfanCount'],
            'swfanDamage' => $data['swfanDamage'],
            'plugCount' => $data['plugCount'],
            'plugDamage' => $data['plugDamage'],
            'other3Details' => $data['other3Details'],
            'other3Count' => $data['other3Count'],
            'other3Damage' => $data['other3Damage']
        ]);

        $stmt = $this->db->query($sql, $updateParams);
        return $stmt->rowCount() > 0;
    }

    /**
     * ดึงข้อมูลเฉพาะ term/pee ถ้าระบุ, ถ้าไม่ระบุจะดึงทั้งหมด
     */
    public function listByTeacher($teach_id, $term = null, $pee = null)
    {
        $params = ['teach_id' => $teach_id];
        $where = "teach_id = :teach_id";
        if ($term !== null && $pee !== null) {
            $where .= " AND term = :term AND pee = :pee";
            $params['term'] = $term;
            $params['pee'] = $pee;
        }
        $sql = "SELECT * FROM report_repair WHERE $where ORDER BY AddDate DESC, id DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // เพิ่มสำหรับเจ้าหน้าที่
    public function getAll()
    {
        $sql = "SELECT * FROM report_repair ORDER BY AddDate DESC, id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * อัปเดตเฉพาะ status ด้วย id (สำหรับเจ้าหน้าที่)
     */
    public function updateStatusById($id, $status)
    {
        $sql = "UPDATE report_repair SET status = :status WHERE id = :id";
        $stmt = $this->db->query($sql, ['status' => $status, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
