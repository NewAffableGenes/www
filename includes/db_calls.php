<?php

function create_assoc($mysqli, $table, $data) {
    $ret = null;
    check_type($table, $data);
    $vars = "";
    $values = "";
    $first = true;
    foreach ($data as $x => $x_value) {
        if ($first) {
            $first = false;
        } else {
            $vars .= ", ";
            $values .= ", ";
        }
        $vars .= "$x";
        if ($x_value === null) {
            $values .= "null";
        } else if ($x_value === true) {
            $values .= "true";
        } else if ($x_value === false) {
            $values .= "false";
        } else {
            $values .= "'$x_value'";
        }
    }
    $query = "INSERT INTO $table ($vars) VALUES ($values)";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (create_assoc): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $ret = $mysqli->insert_id;
    }
    return $ret;
}

function delete_entry($mysqli, $type, $id, $linked_id = null) {
    // This variable is true to delete the actual object but is set false if its a linked object and its used elsewhere
    $delete_entry = true;
    // If it's a linked type it gets special treatment. First check if its linked
    $linked = (($type == 'media') || ($type == 'note'));
    // If it is linked delete the links from where it is referenced
    if ($linked) {
        $query = "SELECT id FROM " . $type . "_link WHERE " . $type . "_id = $id AND belongs_to_class = '$type' AND belongs_to_id = $linked_id";
        $result = $mysqli->query($query);
        $res = $result->fetch_all();
        // delete the links
        foreach ($res as $row) {
            $query = "DELETE FROM " . $type . "_link WHERE id=$row[0]";
            $mysqli->query($query);
        }
        // see if this linked object is still linked from elsewhere
        $query = "SELECT id FROM " . $type . "_link WHERE " . $type . "_id = $id";
        $result = $mysqli->query($query);
        $delete_entry = ($result->num_rows == 0);
    }

    // Now if it really needs deleting...
    if ($delete_entry) {
        $query = "DELETE FROM $type WHERE id=$id";
        $ret = ($mysqli->query($query) == TRUE);
        if (!$ret) {
            log_error("SQL error (delete_entry): $mysqli->error");
            log_error("SQL query: $query");
        }
    }
}

function delete_rows($mysqli, $tree_id, $table) {
    $query = "DELETE FROM $table WHERE tree_id = " . $tree_id;
    $result = ($mysqli->query($query) !== false);
    if ($result === false) {
        log_error("SQL error (delete_rows): $mysqli->error");
        log_error("SQL query: $query");
    }
    return $result;
}

function set_root($mysqli, $root, $treeId) {
    $mysqli->query("UPDATE tree SET root = '$root' WHERE id = '$treeId'");
}

function get_object_id_from_label($mysqli, $treeId, $type, $label) {
    $id = null;
    $query = "SELECT id FROM $type WHERE tree_id = $treeId AND label = '$label' LIMIT 1";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_row();
        $id = $row[0];
    }
    return intval($id);
}

function get_famSLabels($mysqli, $id, $treeId) {
    $famSLabels = [];
    $objects = [];
    $query = "SELECT label FROM family WHERE tree_id = $treeId AND (husband = " . $id . " OR wife = " . $id . ")";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_famS where I'm husband): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $objects = $result->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($objects as &$object) {
        check_type('family', $object);
        array_push($famSLabels, $object['label']);
    }
    return $famSLabels;
}

function get_spouses($mysqli, $id, $treeId, $lspouse, $rspouse) {
    // Returns a 2D array spouses. Each row has 4 elements:
    // - Family description
    // - ID of the family - cannot be null
    // - Display status of the spouse: l = Left, r = Right, x = neither
    // - ID of the spouse - Could be null
    $famS = [];
    $objects = [];
    $query = "SELECT * FROM family WHERE tree_id = $treeId AND husband = " . $id;
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_spouses where I'm husband): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $objects = $result->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($objects as &$object) {
        check_type('family', $object);
        $line = array("", "", "", "");
        $spouse = $object['wife'];
        $line[0] = familyDescription($mysqli, $object);
        $line[1] = strval($object['id']);
        if ($spouse != null) {
            if ($spouse == $lspouse) {
                $line[2] = 'l';
            } else if ($spouse == $rspouse) {
                $line[2] = 'r';
            } else {
                $line[2] = 'x';
            }
            $line[3] = strval($spouse);
        }
        array_push($famS, $line);
    }
    $objects = [];
    $query = "SELECT * FROM family WHERE tree_id = $treeId AND wife = " . $id;
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_spouses where I'm wife): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $objects = $result->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($objects as &$object) {
        check_type('family', $object);
        $line = array("", "", "", "");
        $spouse = $object['husband'];
        $line[0] = familyDescription($mysqli, $object);
        $line[1] = strval($object['id']);
        if ($spouse != null) {
            if ($spouse == $lspouse) {
                $line[2] = 'l';
            } else if ($spouse == $rspouse) {
                $line[2] = 'r';
            } else {
                $line[2] = 'x';
            }
            $line[3] = strval($spouse);
        }
        array_push($famS, $line);
    }
    usort($famS, function($a, $b) {
        return $a[0] > $b[0];
    });
    return $famS;
}

function get_birth($mysqli, $id, $treeId) {
    $ret = null;
    $query = "SELECT * FROM event WHERE "
    . "tree_id = $treeId AND type ='BIRT' AND "
    . "belongs_to_class = 'individual' AND "
    . "belongs_to_id = $id AND event_date_id is not null LIMIT 1";
    $result = $mysqli->query($query);
    if ($result !== false) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $ret = new CDateValue();
            $ret->ReadDatabase($mysqli, $row['event_date_id']);
        }
    }
    return $ret;
}

function get_death($mysqli, $id, $treeId) {
    $ret = null;
    $result = $mysqli->query("SELECT * FROM event WHERE "
            . "tree_id = $treeId AND type ='DEAT' AND "
            . "belongs_to_class = 'individual' AND "
            . "belongs_to_id = $id AND event_date_id is not null LIMIT 1");
    if ($result !== false) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $ret = new CDateValue();
            $ret->ReadDatabase($mysqli, $row['event_date_id']);
        }
    }
    return $ret;
}

function get_children($mysqli, $familyId, $treeId) {
    $query = "SELECT * FROM individual WHERE tree_id = $treeId AND child_in_family = " . $familyId;
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_children): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $children = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($children as &$child) {
            check_type('individual', $child);
        }
        usort($children, function($a, $b) {
            return intval($a['place_in_family_sibling_list']) - intval($b['place_in_family_sibling_list']);
        });
    }
    return $children;
}

function get_all_descriptions($type, $mysqli, $treeId) {
    $ret = [];
    $query = "SELECT * FROM " . $type . " WHERE tree_id = $treeId";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_all_descriptions): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        if ($result->num_rows > 0) {
            $array = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($array as &$value) {
                switch ($type) {
                    case "family" :
                        check_type('family', $value);
                        $ret[$value['id']] = familyDescription($mysqli, $value);
                        break;
                    case "individual" :
                        check_type('individual', $value);
                        $ret[$value['id']] = individualDescription($value);
                        break;
                    case "note" :
                        check_type('note', $value);
                        $ret[$value['id']] = noteDescription($value);
                        break;
                    case "media" :
                        check_type('media', $value);
                        $ret[$value['id']] = MediaBriefDescriptor($value);
                        break;
                    case "source" :
                        check_type('source', $value);
                        $ret[$value['id']] = SourceBriefDescriptor($value);
                        break;
                    case "submitter" :
                        check_type('submitter', $value);
                        $ret[$value['id']] = $value['name'];
                        break;
                    default :
                        log_error("get_all_descriptions() called with type = " . $type);
                        break;
                }
            }
        }
    }
    return $ret;
}

function get_user_by_email($mysqli, $email) {
    $userId = null;
    $query = "SELECT id FROM user WHERE email = '$email' LIMIT 1";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_user_by_email): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_row();
            $userId = intval($row[0]);
        }
    }
    return $userId;
}

function get_user_by_username($mysqli, $username) {
    $userId = null;
    $query = "SELECT id FROM user WHERE username = '$username' LIMIT 1";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (get_user_by_username): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_row();
            $userId = intval($row[0]);
        }
    }
    return $userId;
}

function read_assoc($mysqli, $table, $id) {
    $row = null;
    $query = "SELECT * FROM $table WHERE id = $id LIMIT 1";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (read_assoc): $mysqli->error\n, SQL query: $query");
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }
        else 
        {
            echo 'Entry not found ' . $query . '<br>';
        }
    }
    check_type($table, $row);
    return $row;
}

function read_all_rights($mysqli, $giver, $receiver) {
    $objects = [];
    $table = 'rights';
    if ($giver == null) {
        $query = "SELECT * FROM rights WHERE rights_receiver = '$receiver'";
    } elseif ($receiver == null) {
        $query = "SELECT * FROM rights WHERE rights_giver = '$giver'";
    } else {
        $query = "SELECT * FROM rights WHERE rights_giver = '$giver' AND rights_receiver = '$receiver'";
    }
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (read_all_rights): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $objects = $result->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($objects as &$data) {
        check_type($table, $data);
    }
    return $objects;
}

function read_rights($mysqli, $giver, $receiver) {
    $row = null;
    $query = "SELECT * FROM rights WHERE rights_giver = '$giver' AND rights_receiver = '$receiver' LIMIT 1";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (read_rights): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }
    }
    if ($row != null) {
        check_type('rights', $row);
    }
    return $row;
}

function set_user_tree($mysqli, $userId, $treeId) {
    $mysqli->query("UPDATE user SET tree_id = '$treeId' WHERE id = '$userId'");
}

function read_chunk($mysqli, $id, $i) {
    $row = [];
    $query = "SELECT * FROM chunk WHERE user_id = $id AND count = $i LIMIT 1";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (read_chunk): $mysqli->error");
        log_error("SQL query: $query");
        die();
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }
    }
    check_type('chunk', $row);
    return $row;
}

function clear_log($mysqli) {
    $query = "DELETE FROM log";
    $mysqli->query($query);
}

function delete_chunks($mysqli, $userId) {
// Delete any chunks this user has
    $query = "DELETE FROM chunk WHERE user_id = $userId";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (delete_chunks): $mysqli->error");
        log_error("SQL query: $query");
    }
    update_assoc($mysqli, 'user', $userId, [
        'upload_filename' => null,
        'upload_filesize' => null,
        'upload_numchunk' => null,
        'upload_hash' => null,
        'upload_lastchunk' => null
    ]);
}

function read_all_assoc($mysqli, $table, $treeId = null) {
    $objects = [];
    $query = "SELECT * FROM $table";
    if ($treeId != null) {
        $query .= " WHERE tree_id = $treeId";
    }
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (read_all_assoc): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $objects = $result->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($objects as &$data) {
        check_type($table, $data);
    }
    return $objects;
}

function read_all_assoc_that_belong($mysqli, $table, $treeId, $type, $id) { // Used for FAL
    $objects = [];
    $query = "SELECT * FROM $table WHERE tree_id=$treeId AND belongs_to_class='$type' AND belongs_to_id=$id";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (read_all_assoc_that_belong): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $objects = $result->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($objects as &$data) {
        check_type($table, $data);
    }
    return $objects;
}

function update_assoc($mysqli, $table, $id, $data) {
    check_type($table, $data);
    $values = "";
    $first = true;
    foreach ($data as $x => $x_value) {
        if ($first) {
            $first = false;
        } else {
            $values .= ", ";
        }
        $values .= "$x = ";
        if ($x_value === null) {
            $values .= "null";
        } else if ($x_value === true) {
            $values .= "true";
        } else if ($x_value === false) {
            $values .= "false";
        } else {
            if (gettype($x_value) == "string") {
                $str = str_replace('\'', '\\\'', $x_value);
                $values .= "'$str'";
            } else {
                $values .= "'$x_value'";
            }
        }
    }
    $query = "UPDATE $table SET $values WHERE id=$id";

    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (update_assoc): $mysqli->error");
        log_error("SQL query: $query");
    }
}

function do_log($mysqli, $log_text) {
    $current_time = date("Y-m-d H:i:s");
    $userId = -1;
    if (isset($_SESSION['userId'])) {
        $userId = $_SESSION['userId'];
    }
    create_assoc($mysqli, 'log', [
        'written_by' => $userId,
        'log_text' => $log_text,
        'create_time' => $current_time
    ]);
}

function getAndCheckId($mysqli, $passVar, $type, $treeId) {
    $id = filter_input(INPUT_GET, $passVar, FILTER_VALIDATE_INT);
    if ($id == null) {
        $id = null;
    } elseif ($id == false) {
        $id = null;
    } else {
        if (strlen($type) > 0) {
            $query = "SELECT tree_id FROM $type WHERE id = $id LIMIT 1";
            $result = $mysqli->query($query);
            if ($result->num_rows == 0) {
                $id = null;
            } else {
                $row = $result->fetch_row();
                if ($row[0] != $treeId) {
                    $id = null;
                }
            }
        }
    }
    return $id;
}

function nextLabel($mysqli, $treeId, $type) {
    $maxIndex = 0;
    $query = "SELECT label FROM $type WHERE tree_id = $treeId";
    $result = $mysqli->query($query);
    if ($result === false) {
        log_error("SQL error (next $type label): $mysqli->error");
        log_error("SQL query: $query");
    } else {
        $labels = $result->fetch_all();
        foreach ($labels as $value) {
            $val = intval($value[0]);
            if ($val > $maxIndex) {
                $maxIndex = $val;
            }
        }
    }
    $maxIndex++;
    return strval($maxIndex);
}
