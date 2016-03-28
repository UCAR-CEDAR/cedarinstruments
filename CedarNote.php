<?php
/**
 * @package MediaWiki
 */

class CedarNote
{
    static private $dbuser = "madrigal" ;
    static private $dbpwd = "shrot-kash-iv-po" ;

    static function addScripts()
    {
	global $wgOut ;

	$wgOut->addScript( "<script language=\"javascript\">

	function textareafocus(text_id,default_text) {
	    var elem=document.getElementById(text_id);
	    if (elem)
	    {
		if (elem.value == default_text )
		{
		    elem.value = '';
		}
	    }
	}
	</script>\n" ) ;
    }

    static function displayNote( $note_id, $page_name, $id_name, $id, $previous_note, $dbh )
    {
	global $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$last_note_id = $note_id ;
	if( $note_id != 0 )
	{
	    $allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	    $last_note_id = 0 ;
	    $res = $dbh->query( "select DESCRIPTION, NOTE_DATE, NOTE_USER, NEXT_NOTE, PUBLIC from tbl_notes WHERE ID = $note_id" ) ;
	    if( $res )
	    {
		$obj = $dbh->fetchObject( $res ) ;
		if( $obj )
		{
		    $descript = $obj->DESCRIPTION ;
		    $note_date = $obj->NOTE_DATE ;
		    $note_user = intval( $obj->NOTE_USER ) ;
		    $note_user_name = "" ;
		    if( $note_user )
		    {
			$u = User::newFromId( $note_user ) ;
			$note_user_name = $u->getName() ;
		    }
		    $next_note = $obj->NEXT_NOTE ;
		    $public = $obj->PUBLIC ;
		    if( $public || $allowed )
		    {
			if( $allowed )
			{
			    $wgOut->addHTML( "&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:$page_name?action=edit_note&$id_name=$id&note_id=$note_id'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:$page_name?action=delete_note&note_id=$note_id&previous_note=$previous_note&$id_name=$id'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>\n" ) ;
			}
			$wgOut->addHTML( "$note_date" ) ;
			if( $note_user )
			{
			    $wgOut->addHTML( " - <A HREF=\"$wgServer/wiki/index.php/User:$note_user_name\">$note_user_name</A>" ) ;
			}
			$wgOut->addHTML( " - $descript\n" ) ;
			$wgOut->addHTML( "<BR/>\n" ) ;
			$wgOut->addHTML( "<HR WIDTH='100%' COLOR='Aqua' SIZE='1'>\n" ) ;
		    }
		    if( $next_note )
		    {
			$last_note_id = CedarNote::displayNote( $next_note, $page_name, $id_name, $id, $note_id, $dbh ) ;
		    }
		    else
		    {
			$last_note_id = $note_id ;
		    }
		}
	    }
	}
	return $last_note_id ;
    }

    static function newNoteForm( $last_note_id, $page_name, $id_name, $id )
    {
	global $wgOut, $wgServer, $wgUser ;

	if( $wgUser->isAllowed( 'cedar_admin' ) )
	{
	    $wgOut->addHTML( "<BR />\n" ) ;
	    $wgOut->addHTML( "<FORM name=\"cedarnote\" action=\"$wgServer/wiki/index.php/Special:$page_name\" method=\"POST\">\n" ) ;
	    $wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"action\" value=\"newnote\">\n" ) ;
	    $wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"$id_name\" value=\"$id\">\n" ) ;
	    $wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"last_note_id\" value=\"$last_note_id\">\n" ) ;
	    $wgOut->addHTML( "  &nbsp;&nbsp;<TEXTAREA STYLE=\"width:75%;border-color:black;border-style:solid;border-width:thin;\" onfocus=\"textareafocus('note_description','Add a New Note')\" ID=\"note_description\" NAME=\"note_description\" rows=\"2\" cols=\"20\">Add a New Note</TEXTAREA>\n" ) ;
	    $wgOut->addHTML( "  <BR />\n" ) ;
	    $wgOut->addHTML( "  &nbsp;&nbsp;<INPUT TYPE=\"checkbox\" NAME=\"public\" checked>&nbsp;Note is public, viewable by all?\n" ) ;
	    $wgOut->addHTML( "  <BR />\n" ) ;
	    $wgOut->addHTML( "  &nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Add Note\">\n" ) ;
	    $wgOut->addHTML( "  &nbsp;&nbsp;<INPUT TYPE=\"RESET\" VALUE=\"Reset\">\n" ) ;
	    $wgOut->addHTML( "</FORM>\n" ) ;
	}
    }

    static function newNote( $id_table, $id_name, $id )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to add a note</SPAN><BR />\n" ) ;
	    return false ;
	}

	$note = $wgRequest->getText( 'note_description' ) ;
	$last_note_id = $wgRequest->getInt( 'last_note_id', 0 ) ;
	$public = $wgRequest->getCheck( 'public' ) ;
	$note_user = $wgUser->getID() ;

	$dbh = new DatabaseMysql( $wgDBserver, self::$dbuser, self::$dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$id_table = $dbh->strencode( $id_table ) ;
	$id_name = $dbh->strencode( $id_name ) ;
	$id = $dbh->strencode( $id ) ;
	$note = $dbh->strencode( $note ) ;
	$last_note_id = $dbh->strencode( $last_note_id ) ;
	$public = $dbh->strencode( $public ) ;
	$note_user = $dbh->strencode( $note_user ) ;

	// if last_note_id is 0 then will need to make sure id exists
	if( $last_note_id == 0 )
	{
	    $res = $dbh->query( "select $id_name from $id_table WHERE $id_name = $id" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return false ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "Unable to add a new note for $id_name $id, does not exist or there is more than one<BR />\n" ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return false ;
	    }
	}

	// insert the new note
	$public_value = 0 ;
	if( $public )
	{
	    $public_value = 1 ;
	}
	$insert_success = $dbh->insert( 'tbl_notes',
		array(
			'DESCRIPTION' => $note,
			'NOTE_USER' => $note_user,
			'PUBLIC' => $public_value
		),
		__METHOD__
	) ;

	if( $insert_success == false )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Failed to insert new note<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	// get the id of the new note
	$new_note_id = $dbh->insertId() ;

	// update the last_note_id next_note using that id or set note_id of owner
	$update_success = false ;
	if( $last_note_id == 0 )
	{
	    $update_success = $dbh->update( $id_table,
					    array(
						'NOTE_ID' => $new_note_id ),
					    array(
						$id_name   => $id )
	    ) ;
	}
	else
	{
	    $update_success = $dbh->update( 'tbl_notes',
					    array(
						'NEXT_NOTE' => $new_note_id ),
					    array(
						'ID'   => $last_note_id )
	    ) ;
	}

	if( $update_success == false )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    if( $last_note_id == 0 )
	    {
		$wgOut->addHTML( "Failed to update owner $id<BR />\n" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "Failed to update note $last_note_id<BR />\n" ) ;
	    }
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$dbh->close() ;

	return true ;
    }

    static function deleteNote( $page_name, $id_name, $id, $id_table, $id_table_id )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to delete a note</SPAN><BR />\n" ) ;
	    return false ;
	}

	$note_id = intval( $wgRequest->getInt( 'note_id' ) ) ;
	$previous_note_id = intval( $wgRequest->getInt( 'previous_note' ) ) ;
	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( !$confirm )
	{
	    $wgOut->addHTML( "Are you sure you want to delete the note with id $note_id?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:$page_name?action=delete_note&confirm=yes&$id_name=$id&note_id=$note_id&previous_note=$previous_note_id\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:$page_name?action=delete_note&confirm=no&$id_name=$id\">No</A>)" ) ;
	    return false ;
	}

	if( $confirm && $confirm == "no" )
	{
	    return true ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, self::$dbuser, self::$dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$note_id = $dbh->strencode( $note_id ) ;
	$previous_note_id = $dbh->strencode( $previous_note_id ) ;

	if( $confirm && $confirm == "yes" )
	{
	    // if !previous_note_id && id then update the owner for note_id to specified notes next_note
	    // if previous_note_id then update the previous_note_id to the specified notes next_note

	    // get the next_note id from the note being deleted
	    $res = $dbh->query( "select ID, NEXT_NOTE from tbl_notes WHERE ID = $note_id" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return false ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( !$obj )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return false ;
	    }

	    $retrieved_note_id = intval( $obj->ID ) ;
	    $next_note = intval( $obj->NEXT_NOTE ) ;
	    if( $retrieved_note_id != $note_id )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to delete note $note_id, does not exist<BR />\n" ) ;
		return ;
	    }

	    $update_success = false ;
	    if( $previous_note_id == 0 )
	    {
		$update_success = $dbh->update( $id_table,
						array(
						    'NOTE_ID' => $next_note ),
						array(
						    $id_table_id   => $id,
						    'NOTE_ID' => $note_id )
		) ;
	    }
	    else
	    {
		$update_success = $dbh->update( 'tbl_notes',
						array(
						    'NEXT_NOTE' => $next_note ),
						array(
						    'ID'        => $previous_note_id,
						    'NEXT_NOTE' => $note_id )
		) ;
	    }
	    if( $update_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		if( $previous_note_id == 0 )
		{
		    $wgOut->addHTML( "Failed to update note_id of owner $id:<BR />\n" ) ;
		}
		else
		{
		    $wgOut->addHTML( "Failed to update next_note of note $previous_note_id:<BR />\n" ) ;
		}
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return false ;
	    }

	    // delete the note_id
	    $delete_success = $dbh->delete( 'tbl_notes', array( 'ID' => $note_id ) ) ;

	    if( $delete_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to delete note $note_id:<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return false ;
	    }
	}
	return true ;
    }

    static function editNoteForm( $page_name, $id_name, $id )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit a note</SPAN><BR />\n" ) ;
	    return false ;
	}

	$note_id = intval( $wgRequest->getInt( 'note_id' ) ) ;

	$dbh = new DatabaseMysql( $wgDBserver, self::$dbuser, self::$dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$note_id = $dbh->strencode( $note_id ) ;

	$res = $dbh->query( "select ID, DESCRIPTION, NOTE_DATE, NEXT_NOTE, PUBLIC from tbl_notes WHERE ID = $note_id" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	if( $res->numRows() != 1 )
	{
	    $wgOut->addHTML( "Unable to edit note $note_id, does not exist<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$obj = $dbh->fetchObject( $res ) ;
	if( !$obj )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$description = $obj->DESCRIPTION ;
	$public = intval( $obj->PUBLIC ) ;
	$note_date = $obj->NOTE_DATE ;

	$wgOut->addHTML( "<BR />\n" ) ;
	$wgOut->addHTML( "<FORM name=\"instrumentnote\" action=\"$wgServer/wiki/index.php/Special:$page_name\" method=\"POST\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"action\" value=\"update_note\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"note_id\" value=\"$note_id\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"$id_name\" value=\"$id\">\n" ) ;
	$wgOut->addHTML( "  Note id: $note_id<BR />\n" ) ;
	$wgOut->addHTML( "  Note created on $note_date<BR />\n" ) ;
	$wgOut->addHTML( "  <TEXTAREA STYLE=\"width:75%;border-color:black;border-style:solid;border-width:thin;\" ID=\"note_description\" NAME=\"note_description\" rows=\"2\" cols=\"20\">$description</TEXTAREA>\n" ) ;
	$wgOut->addHTML( "  <BR />\n" ) ;
	if( $public == 0 )
	{
	    $wgOut->addHTML( "  <INPUT TYPE=\"checkbox\" NAME=\"public\">&nbsp;Note is public, viewable by all?\n" ) ;
	}
	else
	{
	    $wgOut->addHTML( "  <INPUT TYPE=\"checkbox\" NAME=\"public\" checked>&nbsp;Note is public, viewable by all?\n" ) ;
	}
	$wgOut->addHTML( "  <BR />\n" ) ;
	$wgOut->addHTML( "  <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Update Note\">\n" ) ;
	$wgOut->addHTML( "  &nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">\n" ) ;
	$wgOut->addHTML( "  &nbsp;&nbsp;<INPUT TYPE=\"RESET\" VALUE=\"Reset\">\n" ) ;
	$wgOut->addHTML( "</FORM>\n" ) ;

	return true ;
    }

    static function updateNote( )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to update this note</SPAN><BR />\n" ) ;
	    return false ;
	}

	$submit = $wgRequest->getText( 'submit' ) ;
	$note = $wgRequest->getText( 'note_description' ) ;
	$note_id = $wgRequest->getInt( 'note_id' ) ;
	$note_user = $wgUser->getID() ;
	$public = $wgRequest->getCheck( 'public' ) ;
	$public_value = 0 ;
	if( $submit == "Cancel" )
	{
	    return true ;
	}

	if( $public )
	{
	    $public_value = 1 ;
	}

	if( !$note_id )
	{
	    $wgOut->addHTML( "Unable to update the note, no note is specified<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, self::$dbuser, self::$dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$note = $dbh->strencode( $note ) ;
	$note_user = $dbh->strencode( $note_user ) ;
	$public_value = $dbh->strencode( $public_value ) ;

	$update_success = $dbh->update( 'tbl_notes',
					array(
					    'DESCRIPTION' => $note,
					    'NOTE_USER' => $note_user,
					    'PUBLIC'      => $public_value ),
					array(
					    'ID'   => $note_id )
	) ;

	if( $update_success == false )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Failed to update note $note_id<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return false ;
	}

	$dbh->close() ;

	return true ;
    }

    static function deleteNotes( $note_id, $dbh )
    {
	global $wgOut ;

	$res = $dbh->query( "select NEXT_NOTE from tbl_notes WHERE ID = $note_id" ) ;
	if( $res )
	{
	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$next_note = intval( $obj->NEXT_NOTE ) ;
		if( $next_note != 0 )
		{
		    CedarNote::deleteNotes( $next_note, $dbh ) ;
		}
	    }
	}

	// delete the instrument
	$delete_success = $dbh->delete( 'tbl_notes', array( 'ID' => $note_id ) ) ;

	if( $delete_success == false )
	{
	    $db_error = $dbh->lastError() ;
	    $wgOut->addHTML( "Failed to delete note $note_id:<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}
    }
}
?>
