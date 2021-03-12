<?php


class Equivalency
{
    private $table = 'search_engine_db';
    private $conn;

    /**
     * The term to search for.
     * @var string
     */
    public $search_term;

    /** The id of the equivalency.
     * @var int
     */
    public $id;

    /** The province the transfer institution is in.
     * @var string
     */
    public $province_code;

    /**
     * Name of the province the transfer institution is in.
     * @var string
     */
    public $province_name;

    /**
     * Code for the transfer institution.
     * @var string
     */
    public $transfer_inst_code;

    /**
     * The name of the transfer institution.
     * @var string
     */
    public $transfer_inst_name;

    /**
     * The subject code at dal.
     * @var string
     */
    public $subject_code;

    /**
     * The name of the subject at dal.
     * @var string
     */
    public $subject_name;

    /**
     * The course name at the transfer institution.
     * @var string
     */
    public $transfer_inst_course;

    /**
     * Number of credits the transfer course if worth.
     * @var string
     */
    public $transfer_credits;

    /**
     * The course name at dal.
     * @var string
     */
    public $dal_course;

    /**
     * Number of credits the dal course is worth.
     * @var string
     */
    public $dal_credits;

    /**
     * The year the equivalency was last assessed.
     * @var string
     */
    public $last_assessed_year;

    /**
     * The semester the equivalency was last assessed.
     * @var string
     */
    public $last_assessed_semester;


    /**
     * Equivalency constructor.
     * @param $conn
     */
    function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Method to get the appropriate string to append to the sql query.
     * @param $string_name string The placeholder to represent the variable in a prepared statement.
     * @param $variable mixed The variable to use in the query.
     * @return string The string to append to the sql query.
     */
    private function getQueryValue($string_name, $variable) {
        return $variable === null ? " $string_name IS NOT NULL " :  " $string_name = :$string_name ";
    }

    /**
     * Gets all courses based on a search query and filters.
     * @return PDOStatement|null A statement full of courses, null when an error occurs.
     */
    function get() {
        if (isset($this->search_term) && !empty($this->search_term)) { // If a search term is provided, use match to find relevant records.
            $query = "SELECT *, MATCH(province_name, transfer_inst_name, subject_code, subject_name, transfer_inst_course, transfer_credits, dal_course, dal_credits, last_assessed_year, last_assessed_semester) AGAINST (:search) as relevance FROM $this->table WHERE MATCH(province_name, transfer_inst_name, subject_code, subject_name, transfer_inst_course, transfer_credits, dal_course, dal_credits, last_assessed_year, last_assessed_semester) AGAINST (:search) AND";
        } else { // If no search term, just base the query on filters below.
            $query = "SELECT * FROM $this->table WHERE";
        }

        // Go through each possible filter and edit the sql query appropriately.
        $query .= $this->getQueryValue('id', $this->id) . "AND";
        $query .= $this->getQueryValue('province_code', $this->province_code) . "AND";
        $query .= $this->getQueryValue('province_name', $this->province_name) . "AND";
        $query .= $this->getQueryValue('transfer_inst_code', $this->transfer_inst_code) . "AND";
        $query .= $this->getQueryValue('transfer_inst_name', $this->transfer_inst_name) . "AND";
        $query .= $this->getQueryValue('subject_code', $this->subject_code) . "AND";
        $query .= $this->getQueryValue('subject_name', $this->subject_name) . "AND";
        $query .= $this->getQueryValue('transfer_inst_course', $this->transfer_inst_course) . "AND";
        $query .= $this->getQueryValue('transfer_credits', $this->transfer_credits) . "AND";
        $query .= $this->getQueryValue('dal_course', $this->dal_course) . "AND";
        $query .= $this->getQueryValue('dal_credits', $this->dal_credits) . "AND";
        $query .= $this->getQueryValue('last_assessed_year', $this->last_assessed_year) . "AND";
        $query .= $this->getQueryValue('last_assessed_semester', $this->last_assessed_semester);

        if (isset($this->search_term) && !empty($this->search_term)) {
            $query .= 'ORDER BY relevance DESC';
        }


        $stmt = $this->conn->prepare($query);

        // If a search term was provided, bind it.
        if (isset($this->search_term) && !empty($this->search_term)) {
            $term = "%$this->search_term%";
            $stmt->bindParam(":search", $term);
        }
        
        // Go through each filter, if a value is provided, bind it.
        if ($this->id !== null)
            $stmt->bindParam(":id", $this->id);
        if ($this->province_code !== null)
            $stmt->bindParam(":province_code", $this->province_code);
        if ($this->province_name !== null)
            $stmt->bindParam(":province_name", $this->province_name);
        if ($this->transfer_inst_code !== null)
            $stmt->bindParam(":transfer_inst_code", $this->transfer_inst_code);
        if ($this->transfer_inst_name !== null)
            $stmt->bindParam(":transfer_inst_name", $this->transfer_inst_name);
        if ($this->subject_code !== null)
            $stmt->bindParam(":subject_code", $this->subject_code);
        if ($this->subject_name !== null)
            $stmt->bindParam(":subject_name", $this->subject_name);
        if ($this->transfer_inst_course !== null)
            $stmt->bindParam(":transfer_inst_course", $this->transfer_inst_course);
        if ($this->transfer_credits !== null)
            $stmt->bindParam(":transfer_credits", $this->transfer_credits);
        if ($this->dal_course !== null)
            $stmt->bindParam(":dal_course", $this->dal_course);
        if ($this->dal_credits !== null)
            $stmt->bindParam(":dal_credits", $this->dal_credits);
        if ($this->last_assessed_year !== null)
            $stmt->bindParam(":last_assessed_year", $this->last_assessed_year);
        if ($this->last_assessed_semester !== null)
            $stmt->bindParam(":last_assessed_semester", $this->last_assessed_semester);


        // Execute the query and return the results.
        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return null;
        }

    }
}