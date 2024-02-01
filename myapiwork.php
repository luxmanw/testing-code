/****************************************** C# code in winforms app
private async Task<bool> PostMyData(Sales sales)
{
	// Sales is the object that holds values to be entered to the database
	// the Uri is https path to web site
    string urli = CommonFunctions.mainURL + "/sales/salescrud.php?typeis=1"; // the uri that php api is there to communicate with database
    var cl = new HttpClient(); // connect to the api through http client
    string js = JsonConvert.SerializeObject(sales); // data should be Serialized to json string
    StringContent httpContent = new StringContent(js, Encoding.UTF8, "application/json"); // preparing application json structure
    var respond = await cl.PostAsync(urli, httpContent); // send data through POST for API
    if (respond.IsSuccessStatusCode) // result is carried here
    {
        return true;
    }
    return false;
}
end of c# code ***************************************/ 

// salescrud.php
<?php
// -- sending to database with two php files
// -- file 1
include_once '../config/database.php';
include_once '../objects/sales.php';
// get database connection
$database = new Database();
$db = $database->getConnection();

$sale = new Sales($db);

if( $_SERVER['REQUEST_METHOD'] == 'POST') {
	$jsonData = file_get_contents('php://input');
	$data = json_decode($jsonData, true);
	if ($_GET["typeis"] == 1){
		if (json_last_error() == 0) 
		{
			//insert
			$sale->id = isset($data["id"]) ? $data["id"] : die('Error is there 0');
			$sale->inv_date = isset($data["inv_date"]) ? $data["inv_date"] : die('Error is there 1');
			$sale->cus_id = isset($data["cus_id"]) ? $data["cus_id"] : die('Error is there 2');
			$sale->inv_number = isset($data["inv_number"]) ? $data["inv_number"] : die('Error is there 3');
			$sale->Contractor = isset($data["Contractor"]) ? $data["Contractor"] : die('Error is there 4');
			$sale->inv_total_novat = isset($data["inv_total_novat"]) ? $data["inv_total_novat"] : die('Error is there 4.1');
			$sale->vat_only = isset($data["vat_only"]) ? $data["vat_only"] : die('Error is there 4.2');
			$sale->PurOrder = isset($data["PurOrder"]) ? $data["PurOrder"] : die('Error is there 5');
			$sale->VatCent = isset($data["VatCent"]) ? $data["VatCent"] : die('Error is there 6');
			$sale->inv_total = isset($data["inv_total"]) ? $data["inv_total"] : die('Error is there 7');
			$sale->sale_type = isset($data["sale_type"]) ? $data["sale_type"] : die('Error is there 8');

			$stmt = false;
			
			try{
				$stmt = $sale->insertSale();
			} catch (Exception $e) {
				echo 'Error executing SQL statement 1 : ' . $e;
			}
			if($stmt){
				echo "Successfull";
			}
			else{
				echo "Error inserting";
			}
		}else{
			http_response_code(400); // Bad Request
			echo "Invalid JSON data received.";
		}
	}
}
// sale.php
class Sales{
 
    // database connection and table name
    private $conn;
    private $table_name = "sales";
 
    // object properties

    public $id;
    public $inv_date;
    public $cus_id;
    public $inv_number;
	public $Contractor;
	public $inv_total_novat;
	public $vat_only;
	public $PurOrder;
	public $VatCent;
	public $tmp_number;
    public $inv_total;
	public $sale_type;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    // insert sale
    function insertSale(){
        // query to insert record
        $query = "INSERT IGNORE INTO
                    sales
					(inv_date, 
					cus_id, 
					inv_number,
					contractor,
					inv_total_novat,
					vat_only,
					purorder,
					vatcent,
					inv_total,
					sale_type)
                VALUES
					(:inv_date, 
					:cus_id, 
					:inv_number,
					:Contractor,
					:inv_total_novat,
					:vat_only,
					:PurOrder,
					:VatCent,
					:inv_total,
					:sale_type)";
        // prepare query
        $stmt = $this->conn->prepare($query);
        // sanitize
		$this->inv_date=htmlspecialchars(strip_tags($this->inv_date));
        $this->cus_id=htmlspecialchars(strip_tags($this->cus_id));
		$this->inv_number=htmlspecialchars(strip_tags($this->inv_number));
		$this->Contractor=htmlspecialchars(strip_tags($this->Contractor));
		$this->inv_total_novat=htmlspecialchars(strip_tags($this->inv_total_novat));
		$this->vat_only=htmlspecialchars(strip_tags($this->vat_only));
		$this->PurOrder=htmlspecialchars(strip_tags($this->PurOrder));
		$this->VatCent=htmlspecialchars(strip_tags($this->VatCent));
        $this->inv_total=htmlspecialchars(strip_tags($this->inv_total));
		$this->sale_type=htmlspecialchars(strip_tags($this->sale_type));
    
        // bind values
		$stmt->bindParam(":inv_date", $this->inv_date);
        $stmt->bindParam(":cus_id", $this->cus_id);
		$stmt->bindParam(":inv_number", $this->inv_number);
		$stmt->bindParam(":Contractor", $this->Contractor);
		$stmt->bindParam(":inv_total_novat", $this->inv_total_novat);
		$stmt->bindParam(":vat_only", $this->vat_only);
		$stmt->bindParam(":PurOrder", $this->PurOrder);
		$stmt->bindParam(":VatCent", $this->VatCent);
        $stmt->bindParam(":inv_total", $this->inv_total);
		$stmt->bindParam(":sale_type", $this->sale_type);
		
        // execute query
		//echo $query;
        if($stmt->execute()){
            //$this->id = $this->conn->lastInsertId();
            return true;
        }
    
        return false;
    }
}
