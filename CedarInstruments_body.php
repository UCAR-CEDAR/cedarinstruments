<?php
class CedarInstruments extends SpecialPage
{
    var $dbuser, $dbpwd ;

    function CedarInstruments() {
	SpecialPage::SpecialPage("CedarInstruments");
	#wfLoadExtensionMessages( 'CedarInstruments' ) ;

	$this->dbuser = "madrigal" ;
	$this->dbpwd = "shrot-kash-iv-po" ;
    }
    
    function execute( $par ) {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$this->setHeaders();

	CedarNote::addScripts() ;

	$sort_param = $wgRequest->getText('sort');
	$sort_by = "KINST" ;
	$action = $wgRequest->getText('action');
	$kinst = $wgRequest->getInt('kinst');
	if( $action == "detail" )
	{
	    $this->instrumentDetail( $kinst ) ;
	    return ;
	}
	else if( $action == "create" )
	{
	    $this->instrumentEdit( $kinst, 1, $action ) ;
	    return ;
	}
	else if( $action == "edit" )
	{
	    $this->instrumentEdit( $kinst, 0, $action ) ;
	    return ;
	}
	else if( $action == "update" )
	{
	    $this->instrumentUpdate( $kinst ) ;
	    return ;
	}
	else if( $action == "delete" )
	{
	    $this->instrumentDelete( $kinst ) ;
	    return ;
	}
	else if( $action == "newnote" )
	{
	    $is_successful = CedarNote::newNote( "tbl_instrument", "KINST", $kinst ) ;
	    if( $is_successful )
		$this->instrumentDetail( $kinst ) ;
	    return ;
	}
	else if( $action == "delete_note" )
	{
	    $is_successful = CedarNote::deleteNote( "CedarInstruments", "kinst", $kinst, "tbl_instrument", "KINST" ) ;
	    if( $is_successful )
	    {
		$this->instrumentDetail( $kinst ) ;
	    }
	    return ;
	}
	else if( $action == "edit_note" )
	{
	    $is_successful = CedarNote::editNoteForm( "CedarInstruments", "kinst", $kinst ) ;
	    if( !$is_successful )
	    {
		$this->instrumentDetail( $kinst ) ;
	    }
	    return ;
	}
	else if( $action == "update_note" )
	{
	    $is_successful = CedarNote::updateNote( ) ;
	    if( $is_successful )
	    {
		$this->instrumentDetail( $kinst ) ;
	    }
	    return ;
	}
	else if( $action == "sort" )
	{
	    if( $sort_param == "kinst" )
	    {
		$sort_by = "KINST" ;
	    }
	    else if( $sort_param == "name" )
	    {
		$sort_by = "INST_NAME" ;
	    }
	    else if( $sort_param == "prefix" )
	    {
		$sort_by = "PREFIX" ;
	    }
	}
	$this->displayInstruments( $sort_by ) ;
    }

    private function displayInstruments( $sort_by )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( $allowed )
	{
	    $wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"0\" WIDTH=\"100%\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
	    $wgOut->addHTML( "	<TR>\n" ) ;
	    $wgOut->addHTML( "	    <TD WIDTH=\"100%\" ALIGN=\"LEFT\">\n" ) ;
	    $wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=create'>Create a New Instrument</A></SPAN>\n" ) ;
	    $wgOut->addHTML( "	    </TD>\n" ) ;
	    $wgOut->addHTML( "	</TR>\n" ) ;
	    $wgOut->addHTML( "    </TABLE>\n" ) ;
	    $wgOut->addHTML( "    <BR/>\n" ) ;
	}

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}
	else
	{
	    // sort_by is created within this code so does not need to
	    // be cleaned
	    $res = $dbh->query( "select KINST, INST_NAME, PREFIX from tbl_instrument ORDER BY $sort_by" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	    else
	    {
		$wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"1\" WIDTH=\"100%\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
		$wgOut->addHTML( "	<TR style=\"background-color:gainsboro;\">\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "	        &nbsp;\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=sort&sort=kinst'>KINST</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=sort&sort=prefix'>Prefix</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"50%\"
		ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=sort&sort=name'>Name</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	</TR>\n" ) ;
		$rowcolor="white" ;
		while( ( $obj = $dbh->fetchObject( $res ) ) )
		{
		    $kinst = $obj->KINST ;
		    $name = $obj->INST_NAME ;
		    $prefix = $obj->PREFIX ;
		    $instr_link = strtolower( $prefix ) ;
		    $wgOut->addHTML( "	<TR style=\"background-color:$rowcolor;\">\n" ) ;
		    if( $rowcolor == "white" ) $rowcolor = "gainsboro" ;
		    else $rowcolor = "white" ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=detail&kinst=$kinst'><IMG SRC='$wgServer/wiki/icons/detail.png' ALT='detail' TITLE='Detail'></A>" ) ;
		    if( $allowed )
		    {
			$wgOut->addHTML( "&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=edit&kinst=$kinst'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=delete&kinst=$kinst'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>\n" ) ;
		    }
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$kinst</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$prefix</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"50%\" ALIGN=\"LEFT\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">&nbsp;&nbsp;&nbsp;$name</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	</TR>\n" ) ;
		}
		$wgOut->addHTML( "</TABLE>\n" ) ;
	    }
	    $dbh->close() ;
	}
    }

    private function instrumentDetail( $kinst )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	$wgOut->addHTML( "<SPAN STYLE=\"font-size:12pt;\">Return to the <A HREF=\"$wgServer/wiki/index.php/Special:CedarInstruments\">instrument list</A></SPAN><BR /><BR />\n" ) ;

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}
	else
	{
	    $kinst = $dbh->strencode( $kinst ) ;
	    $res = $dbh->query( "select INST_NAME, PREFIX, DESCRIPTION, HAS_OBSERVATORY, OBSERVATORY, HAS_CLASS_TYPE, CLASS_TYPE_ID, HAS_OP_MODE, OP_MODE, NOTE_ID from tbl_instrument WHERE KINST = $kinst" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	    else
	    {
		$obj = $dbh->fetchObject( $res ) ;
		if( $obj )
		{
		    $name = $obj->INST_NAME ;
		    $prefix = $obj->PREFIX ;
                    $instr_link = strtolower( $prefix ) ;
		    $descript = $obj->DESCRIPTION ;
		    $has_type = intval( $obj->HAS_CLASS_TYPE ) ;
		    $type = intval( $obj->CLASS_TYPE_ID ) ;
		    $has_obs = intval( $obj->HAS_OBSERVATORY ) ;
		    $obs = intval( $obj->OBSERVATORY ) ;
		    $has_opmode = intval( $obj->HAS_OP_MODE ) ;
		    $opmode = $obj->OP_MODE ;
		    $note_id = intval($obj->NOTE_ID) ;
		    $instr_link = strtolower( $prefix ) ;

		    $wgOut->addHTML( "    <TABLE ALIGN=\"LEFT\" BORDER=\"1\" WIDTH=\"800\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
		    $wgOut->addHTML( "        <TR>\n" ) ;
		    $wgOut->addHTML( "            <TD ALIGN='CENTER' HEIGHT='30px' BGCOLOR='Aqua'>\n" ) ;
		    if( $allowed )
		    {
			$wgOut->addHTML( "                <A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=edit&kinst=$kinst'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarInstruments?action=delete&kinst=$kinst'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>&nbsp;&nbsp;\n" ) ;
		    }
		    $wgOut->addHTML( "                <SPAN STYLE='font-weight:bold;font-size:14pt;'>$kinst - $prefix - $name</SPAN>\n" ) ;
		    $wgOut->addHTML( "            </TD>\n" ) ;
		    $wgOut->addHTML( "        </TR>\n" ) ;
		    $wgOut->addHTML( "        <TR>\n" ) ;
		    $wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
		    $wgOut->addHTML( "                <DIV STYLE='line-height:2.0;font-weight:normal;font-size:10pt;'>\n" ) ;
		    if( $has_type == 1 )
		    {
			$type_info = $this->instrumentType( $type, $dbh ) ;
			$wgOut->addHTML( "                    Instrument Type: $type_info\n" ) ;
		    }
		    else
		    {
			$wgOut->addHTML( "                    Instrument Type: NONE SPECIFIED\n" ) ;
		    }
		    $wgOut->addHTML( "                    <BR />\n" ) ;

		    $obs_info = $this->instrumentObs( $has_obs, $obs, $dbh ) ;
		    $wgOut->addHTML( "                    Observatory: $obs_info\n" ) ;
		    $wgOut->addHTML( "                    <BR />\n" ) ;

		    $site_info = $this->instrumentSite( $kinst, $dbh ) ;
		    $wgOut->addHTML( "                    Observation Site: $site_info\n" ) ;

		    $wgOut->addHTML( "                    <BR />\n" ) ;
		    if( $has_opmode )
		    {
			$wgOut->addHTML( "                    Operating Mode: $opmode\n" ) ;
		    }
		    else
		    {
			$wgOut->addHTML( "                    Operating Mode:\n" ) ;
		    }
		    $wgOut->addHTML( "                    <BR />\n" ) ;
		    $wgOut->addWikiText( "Instrument Page: [[Instruments:$instr_link|$prefix]]" ) ;
		    $wgOut->addHTML( "                    Description:<BR /><SPAN STYLE=\"line-height:1.0;\">$descript</SPAN><BR /><BR />\n" ) ;
		    $wgOut->addHTML( "                </DIV>\n" ) ;
		    $wgOut->addHTML( "            </TD>\n" ) ;
		    $wgOut->addHTML( "        </TR>\n" ) ;
		    $wgOut->addHTML( "        <TR>\n" ) ;
		    $wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
		    $wgOut->addHTML( "                <DIV STYLE='font-weight:normal;font-size:10pt;'>\n" ) ;
		    $wgOut->addHTML( "                    Notes:<BR />\n" ) ;
		    $last_note_id = CedarNote::displayNote( $note_id, "CedarInstruments", "kinst", $kinst, 0, $dbh ) ;
		    CedarNote::newNoteForm( $last_note_id, "CedarInstruments", "kinst", $kinst ) ;
		    $wgOut->addHTML( "                </DIV>\n" ) ;
		    $wgOut->addHTML( "            </TD>\n" ) ;
		    $wgOut->addHTML( "        </TR>\n" ) ;
		    $wgOut->addHTML( "    </TABLE>\n" ) ;
		}
		else
		{
		    $wgOut->addHTML( "There is no instrument with the given id: $kinst<BR />\n" ) ;
		}
	    }

	    $dbh->close() ;
	}
    }

    private function instrumentObs( $has_obs, $obs, $dbh )
    {
	global $wgServer ;

	$ret_obs = "" ;

	if( !$has_obs )
	{
	    $ret_obs = "None" ;
	    return $ret_obs ;
	}

	// obs is created within this script, not passed in by
	// client-code, so does not need to be cleaned
	$res = $dbh->query( "select ALPHA_CODE, LONG_NAME from tbl_observatory WHERE ID = $obs" ) ;
	if( !$res )
	{
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	}
	else
	{
	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$alpha_code = $obj->ALPHA_CODE ;
		$long = $obj->LONG_NAME ;
		$ret_obs = "<A HREF=\"$wgServer/wiki/index.php/Special:Cedar_Observatories?action=detail&obs=$obs\">" . $alpha_code . " - " . $long . "</A>" ;
		$first = false ;
	    }
	    else
	    {
		$ret_obs = "Not Found" ;
	    }
	}

	return $ret_obs ;
    }

    private function instrumentSite( $kinst, $dbh )
    {
	global $wgServer ;

	$ret_site = "" ;

	// kinst is created in this script so does not need to be
	// cleaned
	$res = $dbh->query( "select ID, SHORT_NAME, LONG_NAME from tbl_site WHERE KINST = $kinst" ) ;
	if( !$res )
	{
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	}
	else
	{
	    $first = true ;
	    while( $obj = $dbh->fetchObject( $res ) )
	    {
		$site = intval( $obj->ID ) ;
		$short = $obj->SHORT_NAME ;
		$long = $obj->LONG_NAME ;
		if( !$first )
		    $ret_site .= ", " ;
		$ret_site .= "<A HREF=\"$wgServer/wiki/index.php/Special:Cedar_Sites?action=detail&site=$site\">" . $short . " - " . $long . "</A>" ;
		$first = false ;
	    }
	    if( $first )
	    {
		$ret_site = "None" ;
	    }
	}

	return $ret_site ;
    }

    private function instrumentType( $type, $dbh )
    {
	$ret_type = "" ;
	$next_type = "" ;

	// type is created within this script and does not need to be
	// cleaned
	$res = $dbh->query( "select ID, NAME, PARENT from tbl_class_type WHERE ID = $type" ) ;
	if( !$res )
	{
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	}
	else
	{
	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$parent = intval( $obj->PARENT ) ;
		$name = $obj->NAME ;
		if( $parent != 0 )
		{
		    $next_type = $this->instrumentType( $parent, $dbh ) . " &gt; " ;
		}
		$ret_type = $next_type . $name ;
	    }
	}

	return $ret_type ;
    }

    private function instrumentEdit( $kinst, $isnew, $action )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit instrument information</SPAN><BR />\n" ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$inst_name = "" ;
	$prefix = "" ;
	$description = "" ;
	$has_obs = 0 ;
	$obs = 0 ;
	$has_class_type = 0 ;
	$class_type_id = 0 ;
	$has_op_mode = 0 ;
	$op_mode = 0 ;
	if( $isnew == 0 && $action == "edit" )
	{
	    $kinst = $dbh->strencode( $kinst ) ;
	    $res = $dbh->query( "select INST_NAME, PREFIX, DESCRIPTION, HAS_OBSERVATORY, OBSERVATORY, HAS_CLASS_TYPE, CLASS_TYPE_ID, HAS_OP_MODE, OP_MODE from tbl_instrument WHERE KINST = $kinst" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "Unable to edit the instrument $kinst, does not exist<BR />\n" ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( !$obj )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $inst_name = $obj->INST_NAME ;
	    $prefix = $obj->PREFIX ;
	    $description = $obj->DESCRIPTION ;
	    $has_obs = intval( $obj->HAS_OBSERVATORY ) ;
	    $obs = intval( $obj->OBSERVATORY ) ;
	    $has_class_type = intval( $obj->HAS_CLASS_TYPE ) ;
	    $class_type_id = intval( $obj->CLASS_TYPE_ID ) ;
	    $has_op_mode = intval( $obj->HAS_OP_MODE ) ;
	    $op_mode = $obj->OP_MODE ;
	}
	else if( $action == "update" )
	{
	    $inst_name = $wgRequest->getText( 'inst_name' ) ;
	    $prefix = $wgRequest->getText( 'prefix' ) ;
	    $description = $wgRequest->getText( 'description' ) ;
	    $obs = $wgRequest->getInt( 'obs' ) ;
	    $has_obs = 0 ;
	    if( $obs != 0 )
	    {
		$has_obs = 1 ;
	    }
	    $class_type_id = $wgRequest->getInt( 'class_type_id' ) ;
	    $has_class_type = 0 ;
	    if( $class_type_id != 0 )
	    {
		$has_class_type = 1 ;
	    }
	    $op_mode = $wgRequest->getText( 'op_mode' ) ;
	    $has_op_mode = 0 ;
	}

	$wgOut->addHTML( "<FORM name=\"instrument_edit\" action=\"$wgServer/wiki/index.php/Special:CedarInstruments\" method=\"POST\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"action\" value=\"update\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"kinst\" value=\"$kinst\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"isnew\" value=\"$isnew\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"op_mode\" value=\"0\">\n" ) ;
	$wgOut->addHTML( "  <TABLE WIDTH=\"800\" CELLPADDING=\"2\" CELLSPACING=\"0\" BORDER=\"0\">\n" ) ;

	// instrument kinst text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        KINST:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"new_kinst\" size=\"10\" value=\"$kinst\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// instrument long name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Instrument Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"inst_name\" size=\"30\" value=\"$inst_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// prefix text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Prefix:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"prefix\" size=\"3\" value=\"$prefix\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// Selection list of observatories
	$res = $dbh->query( "select ID, ALPHA_CODE, LONG_NAME from tbl_observatory ORDER BY ALPHA_CODE" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Observatory:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <select name=\"obs\">\n" ) ;
	if( $has_obs == 0 )
	{
	    $wgOut->addHTML( "          <option value=\"\" selected>Select an observatory</option>\n" ) ;
	}
	else
	{
	    $wgOut->addHTML( "          <option value=\"\">Select an observatory</option>\n" ) ;
	}
	while( ( $obj = $dbh->fetchObject( $res ) ) )
	{
	    $id = intval( $obj->ID ) ;
	    $alpha_code = $obj->ALPHA_CODE ;
	    $long_name = $obj->LONG_NAME ;
	    if( $has_obs && $obs == $id )
	    {
		$wgOut->addHTML( "          <option value=\"$id\" selected>$alpha_code - $long_name</option>\n" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "          <option value=\"$id\">$alpha_code - $long_name</option>\n" ) ;
	    }
	}
	$wgOut->addHTML( "        </select>\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// Selection list of class types
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Class Type:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <select name=\"class_type_id\">\n" ) ;
	if( $has_class_type == 0 )
	{
	    $wgOut->addHTML( "          <option value=\"\" selected>Select a class type</option>\n" ) ;
	}
	else
	{
	    $wgOut->addHTML( "          <option value=\"\">Select an class type</option>\n" ) ;
	}
	$this->instrumentClassOptions( 0, "", $has_class_type, $class_type_id, $dbh ) ;
	$wgOut->addHTML( "        </select>\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// description text area
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Description:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <TEXTAREA STYLE=\"width:75%;border-color:black;border-style:solid;border-width:thin;\" ID=\"description\" NAME=\"description\" rows=\"10\" cols=\"20\">$description</TEXTAREA><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// submit, cancel and reset buttons
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Submit\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"RESET\" VALUE=\"Reset\">\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	$wgOut->addHTML( "  </TABLE>\n" ) ;

	// FIXME
	//$wgOut->addHTML( "op_mode: $op_mode - what the heck do I do with this?<BR />\n" ) ;

	$wgOut->addHTML( "</FORM>\n" ) ;

	$dbh->close() ;
    }

    private function instrumentClassOptions( $parent, $indent, $has_class_type,
					     $class_type_id, $dbh )
    {
	global $wgOut ;

	// parent variable is created within this script, so does not need
	// to be cleaned
	$res = $dbh->query( "select ID, NAME from tbl_class_type where PARENT=$parent order by NAME" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}
	if( $res->numRows() > 0 )
	{
	    while( ( $obj = $dbh->fetchObject( $res ) ) )
	    {
		$id = intval( $obj->ID ) ;
		$name = $obj->NAME ;
		if( $has_class_type && $class_type_id == $id )
		{
		    $wgOut->addHTML( "          <option value=\"$id\" selected>$indent$name</option>\n" ) ;
		}
		else
		{
		    $wgOut->addHTML( "          <option value=\"$id\">$indent$name</option>\n" ) ;
		}
		$new_indent = $indent . "--" ;
		if( $parent != $id )
		{
		    $this->instrumentClassOptions( $id, $new_indent,
						   $has_class_type,
						   $class_type_id, $dbh ) ;
		}
	    }
	}

	return ;
    }

    private function instrumentUpdate( $kinst )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit instrument information</SPAN><BR />\n" ) ;
	    return ;
	}

	// if the cancel button was pressed then go to instrument detail
	$submit = $wgRequest->getText( 'submit' ) ;
	if( $submit == "Cancel" )
	{
	    $this->instrumentDetail( $kinst ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$new_kinst = $dbh->strencode( $wgRequest->getInt( 'new_kinst' ) ) ;
	$isnew = $dbh->strencode( $wgRequest->getInt( 'isnew' ) ) ;
	$inst_name = $dbh->strencode( $wgRequest->getText( 'inst_name' ) ) ;
	$prefix = $dbh->strencode( $wgRequest->getText( 'prefix' ) ) ;
	$description = $dbh->strencode( $wgRequest->getText( 'description' ) ) ;
	$obs = $dbh->strencode( $wgRequest->getInt( 'obs' ) ) ;
	$has_obs = 0 ;
	if( $obs != 0 )
	{
	    $has_obs = 1 ;
	}
	$class_type_id = $dbh->strencode( $wgRequest->getInt( 'class_type_id' ) ) ;
	$has_class_type = 0 ;
	if( $class_type_id != 0 )
	{
	    $has_class_type = 1 ;
	}
	$op_mode = $dbh->strencode( $wgRequest->getText( 'op_mode' ) ) ;
	$has_op_mode = 0 ;

	// if editing an kinst, check to make sure the kinst exists
	if( $isnew == 0 )
	{
	    $kinst = $dbh->strencode( $kinst ) ;
	    $res = $dbh->query( "select KINST from tbl_instrument where KINST = $kinst" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "<SPAN STYLE=\"color:red\">The specified kinst $kinst does not exist</SPAN><BR />\n" ) ;
		$this->instrumentEdit( $kinst, $isnew, "update" ) ;
		return ;
	    }
	}

	if( $new_kinst == 0 )
	{
	    $dbh->close() ;
	    $wgOut->addHTML( "<SPAN STYLE=\"color:red\">The specified kinst $new_kinst can not be 0</SPAN><BR />\n" ) ;
	    $this->instrumentEdit( $kinst, $isnew, "update" ) ;
	    return ;
	}

	// if the kinst and new_kinst are not the same, make sure new_kinst doesn't already exist
	if( $kinst != $new_kinst )
	{
	    $res = $dbh->query( "select KINST from tbl_instrument where KINST = $new_kinst" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 0 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "<SPAN STYLE=\"color:red\">The new kinst $new_kinst already exists</SPAN><BR />\n" ) ;
		$this->instrumentEdit( $kinst, $isnew, "update" ) ;
		return ;
	    }
	}

	// if isnew then insert the new instrument
	// if not new, kinst != 0, then update kinst (remember to use new_kinst)
	if( $isnew == 1 )
	{
	    $insert_success = $dbh->insert( 'tbl_instrument',
		    array(
			    'KINST' => $new_kinst,
			    'INST_NAME' => $inst_name,
			    'PREFIX' => $prefix,
			    'DESCRIPTION' => $description,
			    'HAS_OBSERVATORY' => $has_obs,
			    'OBSERVATORY' => $obs,
			    'HAS_CLASS_TYPE' => $has_class_type,
			    'CLASS_TYPE_ID' => $class_type_id,
			    'HAS_OP_MODE' => $has_op_mode,
			    'OP_MODE' => $op_mode
		    ),
		    __METHOD__
	    ) ;

	    if( $insert_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to insert new instrument $new_kinst<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}
	else if( $isnew == 0 )
	{
	    $update_success = $dbh->update( 'tbl_instrument',
		    array(
			    'KINST' => $new_kinst,
			    'INST_NAME' => $inst_name,
			    'PREFIX' => $prefix,
			    'DESCRIPTION' => $description,
			    'HAS_OBSERVATORY' => $has_obs,
			    'OBSERVATORY' => $obs,
			    'HAS_CLASS_TYPE' => $has_class_type,
			    'CLASS_TYPE_ID' => $class_type_id,
			    'HAS_OP_MODE' => $has_op_mode,
			    'OP_MODE' => $op_mode
		    ),
		    array(
			    'KINST' => $kinst
		    ),
		    __METHOD__
	    ) ;

	    if( $update_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to update kinst $kinst<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->instrumentDetail( $new_kinst ) ;
    }

    private function instrumentDelete( $kinst )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to delete instruments</SPAN><BR />\n" ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	// kinst is created in this script so does not need to be
	// cleaned
	$res = $dbh->query( "select ID, SHORT_NAME, LONG_NAME from tbl_site WHERE KINST = $kinst" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}
	if( $res->numRows() > 0 )
	{
	    $wgOut->addHTML( "<SPAN STYLE='color:red;font-size:12pt;font-weight:bold;'>Unable to delete this instrument, it is referenced by sites \n" ) ;
	    $first = true ;
	    while( $obj = $dbh->fetchObject( $res ) )
	    {
		$short_name = $obj->SHORT_NAME ;
		if( !$first )
		    $wgOut->addHTML( ", " ) ;
		$wgOut->addHTML( "$short_name" ) ;
		$first = false ;
	    }
	    $wgOut->addHTML( "</SPAN><BR /><BR />\n" ) ;
	    $dbh->close() ;
	    $this->instrumentDetail( $kinst ) ;
	    return ;
	}


	// if confirm_delete is not set or is false then go to the instrument detail for this instrument
	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( !$confirm )
	{
	    $wgOut->addHTML( "Are you sure you want to delete the instrument with kinst $kinst?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:CedarInstruments?action=delete&confirm=yes&kinst=$kinst\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:CedarInstruments?action=delete&confirm=no&kinst=$kinst\">No</A>)" ) ;
	    return ;
	}

	if( $confirm && $confirm == "no" )
	{
	    $this->instrumentDetail( $kinst ) ;
	    return ;
	}

	// if confirm_delete is true then delete the instrument
	if( $confirm && $confirm == "yes" )
	{
	    // need to delete all of the associated notes as well
	    $kinst = $dbh->strencode( $kinst ) ;
	    $res = $dbh->query( "select NOTE_ID from tbl_instrument WHERE KINST = $kinst" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$note_id = intval( $obj->NOTE_ID ) ;
		if( $note_id != 0 )
		{
		    CedarNote::deleteNotes( $note_id, $dbh ) ;
		}
	    }

	    // delete the instrument
	    $kinst = $dbh->strencode( $kinst ) ;
	    $delete_success = $dbh->delete( 'tbl_instrument', array( 'KINST' => $kinst ) ) ;

	    if( $delete_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to delete instrument $kinst:<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->displayInstruments( "KINST" ) ;
    }
}

?>
