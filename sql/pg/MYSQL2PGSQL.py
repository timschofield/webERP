#/usr/bin/env python

# The Rules

# This program was written by Danie Brink brink@nas.co.za
# please note the software is distributed under the :
# GPL Licence, please see the GPL Licence in README.txt
# I take no responsibility consiquetial or 
# inconsequential for any damges that may result from 
# the use of this software.



###########################
## CODE STARTS HERE
###########################
import sys
from string import *
from fileinput import FileInput
from array import *

###########################
## Globals
###########################
#If you want to retain the FieldName and TableName UpperAndLowerCase set to true
G_UseCase = False
# Store the current table name here
CurrentTableName = ""
# Store the current field name here
CurrentFieldName = ""
# Store current field type is serial
TypeIsSerial = False
# All serial keys for tables are added to this array please see code for structure
SerialKeys = []
# Data type of current field requires special handling of type
# 0=Double Qoutes 1=Single Qoutes 2=NoQoutes
LineDataReqQoutes = 0
# Array has field types whom in MySQL uses Length Identifiers but not in PSQL
# Strip Length from following types
NonArrayTypes = ( "int", "smallint", "bigint", "tinyint" )
# Array has field types whom in MySQL uses and is 
#   not supported by  PSQL and is substituted with
# These are types that must be substituted 
ConversionTypes = ( 
	("tinyint","smallint"),("double(","numeric("),
	("double","float8"),("float(","numeric("),
	("float","float8"),("datetime","timestamp"),
	("longblob","text")
	)
# Need to handle default values of fields of this type as such
# 0 Means StringTypes, 1 Means Numeric Types, 
# 2 Means Float Types, 3 Date, 4 Time, 5 DateTime
# 10 Means Serial Types with No Default
# -1 Unknown 
DefaultValueConversionTypes = ( ( "char", 0 ),( "varchar", 0),
	( "text", 0 ), ( "smallint", 1),
	( "int", 1), ( "bigint", 1),
	( "decimal", 2), ( "numeric", 2),
	( "real", 2), ( "float8",2),
	( "serial", 10), ( "bigserial", 10),
	( "timestamp", 5),( "date", 3), ( "time", 4)
	)
# These are the current fields of the active table
CurrentFields = []

# Used by Process Line and supporting functions
HasPrinted = False
InCreateTable = False
NeedComma = False
HasInsert = False


###########################
## The Program
###########################

# easier to call a function than declare global variables
def UseCase() :
	return G_UseCase

def LoadFile( FileName ):
	Result = FileInput( FileName )
	return Result
	
def CloseFile( File ):
	del File
	
def PrintFile( File ):
	# prcess Each line
	for line in File:
		line = rstrip(line)
		ProcessLine( line )
	UpdateSerialKeys()
		
		
		
def Comment( line ):
	# We ignore all comments
	NOP = ""

		
def BeginCreateTable( line ) :
	global CurrentFields, CurrentTableName
	line = lstrip(line[12:]);
	line = rstrip(line[:-1]);
	CurrentTableName = line[1:-1];
	if UseCase() :
		line = "\""+line[1:-1]+"\""
	else :
		line = line[1:-1]
	print "CREATE TABLE",line,"("
	CurrentFields = []
	
def EndCreateTable( line ) :
	global CurrentFields
	DoNoComma()
	print ");"
	print ""


def ConvertVar( line, withtab=True ):
	global CurrentFieldName
	CurrentFieldName = ""
	if ( line[0:1] == "`" ) :
		I = find( line[1:], "`" );
		if ( I <> -1 ) :
			FieldName = line[1:I+1]
			remainder = lstrip(line[1+I+1:])
			if UseCase() :
				variable = "\""+FieldName+"\""
			else :
				variable = ""+FieldName+""
			if withtab :
				CurrentFieldName = FieldName
				print "\t",
				print ljust(variable,20),
			else :
				print variable,
			line = remainder
	return line

def ConvertVarInBrackets( line ) :
	l = find( line, "(" )+1
	r = rfind( line, ")" )
	rem = line[r+1:]
	NeedSubComma = False;
	print "(",
	while True :
		if NeedSubComma :
			print ",",
		line = ConvertVar(line[l:r-l+1], False)
		NeedSubComma = True
		if line == "" :
			break
	print ")",rem,
	

def PrimaryKey( line ):
	print "\t",line[0:11],
	line = strip(line[12:])
	ConvertVarInBrackets( line )
	
def DoComma() :
	global NeedComma
	if NeedComma :
	  print ","
	else :
	  print ""
	
def DoNoComma():
	global NeedComma
	if NeedComma :
	  print ""		  


# Some value conversion / handling functions
def StripQuotes( Val ) :
	Val = strip( Val )
	if Val[0:1] in ["\"","'","`"] :
		Val = Val[1:]
		if Val[len(Val)-1:] in ["\"","'","`"]:
			Val = Val[:-1]
	return Val
	
def ValueToNum( Val ) :
	Val = StripQuotes(Val)
	return Val

def ValueToFloat( Val ) :
	return ValueToNum( Val )

def ValueToDate( Val, AddQoutes = True ) :
	Val = StripQuotes(Val)
	if Val == "0000-00-00" :
		Val = ""
	elif AddQoutes :
		Val = "'"+Val+"'"
	return Val

def ValueToTime( Val, AddQoutes = True ) :
	Val = StripQuotes(Val)
	if Val == "00:00:00" :
		Val = ""
	elif AddQoutes :
		Val = "'"+Val+"'"
	return Val

def ValueToDateTime( Val, AddQoutes = True ) :
	Val = StripQuotes(Val)
	I = find( Val, " " )
	if I > 0 :
		Dt = ValueToDate(Val[0:I], False)
		Tm = ValueToTime(Val[I+1:], False)
		if Dt+Tm == "" :
			Val = ""
		elif Tm == "" :
			Val = Dt
		else :
			Val = Tm
		
	if Val <> "" and AddQoutes :
		Val = "'"+Val+"'"
	return Val



def GetFieldType( ValueType ) :
	global DefaultValueConversionTypes
	DataType = -1
	for val in DefaultValueConversionTypes :
		if find( strip(lower(ValueType)), val[0] ) == 0 :
			DataType = val[1]
			break
	return DataType;

def ChangeDefaultValue( ValueType, DefaultValue ) :
	global CurrentFields
	global CurrentFieldName
	DataType = GetFieldType( ValueType );
	Res = ""
	if strip(DefaultValue) <> "NULL" :
		if DataType == 0 :
			Res = "'"+StripQuotes(DefaultValue)+"'"
		elif DataType == 1 :
			Res = ValueToNum(DefaultValue)
		elif DataType == 2 :
			Res = ValueToFloat(DefaultValue)
		elif DataType == 3 :
			Res = ValueToDate(DefaultValue)
		elif DataType == 4 :
			Res = ValueToTime(DefaultValue)
		elif DataType == 5 :
			Res = ValueToDateTime(DefaultValue)
		elif DataType == -1 :
			print "UNKNOWN DATATYPE "+ValueType
	if Res <> "" :
		Res = "DEFAULT " + Res
	return Res

def ChangeType( word ) :
	global TypeIsSerial, LineDataReqQoutes
	LineDataReqQoutes = 0
	if TypeIsSerial :
		TypeIsSerial = False
		LineDataReqQoutes = 2
		return "serial"
	for val in NonArrayTypes :
		if find( lower(word), val ) == 0 :
			word = val
			break
	for val in ConversionTypes :
		if find( lower(word), val[0] ) == 0 :
			word = replace(word, val[0], val[1])
			break		
	return strip(word)



def CheckTypeDefs(line):
	global CurrentFields, CurrentFieldName, CurrentTableName, SerialKeys
	words = split(line)
	line = ""
	Skip = True
	NextIsDefault = False
	FirstDefault = False
	IsNull = False
	IsNot = False
	HasDefault = False
	DataType = ""
	DefaultValue = ""
	for word in words :
	  if Skip :
		DataType = ChangeType( word )
		# Log Fields with fieldtypes
		FieldType = GetFieldType(DataType)
		CurrentFields += [[CurrentFieldName, FieldType ]]
		if FieldType == 10 : # serial keys needs to be remembered
			SerialKeys += [[CurrentTableName,CurrentFieldName,len(CurrentFields), 0]] # table, field, index, val
		Skip = False
	  elif NextIsDefault :
		if (not FirstDefault) and word[0:1] == "'" :
			FirstDefault = True
			word = "\""+word[1:]
		if FirstDefault and word[len(word)-1:] == "'" :
			NextIsDefault = False
			FirstDefault = False
			word = word[:-1]+"\""
		DefaultValue += word + " "
	  else :
		if upper(word) == "NOT" :
			IsNot = True
		elif upper(word) == "NULL" :
			IsNull = True
		elif upper(word) == "DEFAULT" :
			HasDefault = True
			NextIsDefault = True
		else :
			line += word + " "
	Result = DataType
	if HasDefault :
		DefaultStr = ChangeDefaultValue(DataType,DefaultValue)
		if (DefaultStr == "") :
			if IsNot :
				IsNot = False
				IsNull = False

	if IsNot :
		Result += " NOT NULL"
	elif IsNull :
		Result += " NULL"
	if HasDefault :
		Result += " "+DefaultStr
	if line <> "" :
		Result += " " + line
	return Result


def CheckSpecial(line):
	global TypeIsSerial
	Pos = find( line, "auto_increment" )
	if Pos >= 0 :
		TypeIsSerial = True;
		line = replace( line, "auto_increment", "" )
	return line
		


def DoCreateTable( line ) :
	global NeedComma
	if (line[0:1] == "`") :
		DoComma()
		line = ConvertVar(line)
		line = CheckSpecial(line)
		line = CheckTypeDefs(line)
		NeedComma = True
	elif (line[0:3] == "KEY" or line[0:10] == "UNIQUE KEY" or line[0:10] == "CONSTRAINT" ) :
		# ignore indexes and constraints
		line = ""
	elif (line[0:11] == "PRIMARY KEY" ) :
		DoComma()
		PrimaryKey(line)
		line = ""
	if ( line <> "" ) :
		print "\t",line,
	
def FindDataType( FieldName ) :
	global CurrentFields
	for val in CurrentFields :
		if FieldName == val[0] :
			return val[1]
	return -1

def ConvertValueToType( Value, FieldType ) :
	Ret = Value # Pre Assign
	if Ret <> "NULL" :
		if FieldType == 0 : #string
			Ret = replace(Value[1:-1],"\t","\\t")
		elif FieldType in [1,10] :
			Ret = Value
		elif FieldType in [2,3,4,5] :
			Ret = StripQuotes(Value)
	
	# worry about NULL and blank dates
	if ( Ret == "NULL" ) or \
	   (FieldType == 3 and Ret == "0000-00-00") or \
	   (FieldType == 4 and Ret == "00:00:00") or \
	   (FieldType == 5 and Ret == "0000-00-00 00:00:00") :
		Ret=""

	if Ret == "" and FieldType <> 0 : #not strings
		Ret = "\\N"
	return Ret

# Extracts only values form INSERT statement
# Strips the Fields and other clutter from values in normal insert
def GetValues( line ) :
	p = find( line,"(")
	line = line[p+1:]
	p = find( line,")")
	line = line[p+1:]
	p = find( line,"(")
	line = line[p+1:]
	p = rfind( line,")")
	line = line[0:p]
	return line

# builds up an array of fields in the order values would be provided
def FindFields(line) :
	global CurrentFields
	Res = []
	I = 0
	for val in CurrentFields :
		Res += [val[0]]
		I +=1
	return Res

# Reconstruct values as they should be, we spit by "," 
#   but strings may contain them so we reconstruct
def FindValues(line) :
	values = GetValues(line)
	words = split(values,",")
	InWord = False
	newwords = []
	CurrWord = ""
	for word in words :
		work = strip(word)
		if InWord :
			if (work == "'") or (work[len(work)-1:] == "'" and work[len(work)-2:]) <> "\'":
				InWord = False
			CurrWord += ","+word #need to keep spacing
			if not InWord :
				CurrWord = strip(CurrWord) #remove spacing at ends
		elif work[0:1] == "'" and \
		  work[len(work)-1:] == "'" and \
		  work[len(work)-2:] <> "\'" : # Ok standalone string
			CurrWord = work
			InWord = False
		elif work[0:1] == "'" : # OK starting split string
			CurrWord = word
			InWord = True
		else :
			CurrWord = work
		if not InWord :
			newwords += [CurrWord]
			CurrWord = ""
	words = newwords # fix string problems
	Res = [];
	for val in words :
		val = strip(val);
		Res += [val]
	return Res

# Finds out if the current table has a Serial Key / AUTO_INC field
def FindSerial ():
	global CurrentTableName, SerialKeys
	I = 0;
	for val in SerialKeys :
		if val[0] == CurrentTableName :
			return I
		I += 1
	return -1

# Update Serial keys in the database
def UpdateSerialKeys() :
	global SerialKeys
	for val in SerialKeys :
		try :
			S = str( atoi(val[3])+1)
		except :
			S = str( val[3]+1)
		if UseCase() :
			print "Select pg_catalog.setval( '\""+val[0]+"_"+val[1]+"_seq\"', "+S+", false );"
		else :
			print "Select pg_catalog.setval( '"+val[0]+"_"+val[1]+"_seq', "+S+", false );"

	
# create COPY Statements for each insert
# 	this is done for two reasons
#		first it is easier to do
#		secondly its safer as a error wont lose all the data
def DoCopy(beg) :
	global CurrentTableName, CurrentFields, SerialKeys
	print "COPY",
	if UseCase() :
		print("\""+CurrentTableName+"\""),
	else :
		print CurrentTableName,
	print "(",
	I = 0
	SerKeyIdx = FindSerial()
	FldIdx = -1 # Used for finding serial key values
	for val in CurrentFields :
		if I > 0 :
			print ",",
		if UseCase() :
			print ("\""+val[0]+"\""),
		else :
			print (""+val[0]+""),
		if SerKeyIdx >= 0 :
			if val[0] == SerialKeys[SerKeyIdx][1] : #Compare Fields
				FldIdx = I
		I+=1
	print ") FROM stdin;"
	fields = []
	values = []
	fields = FindFields(beg)
	values = FindValues(beg)
	I = 0
	line = ""
	for field in fields :
		if I > 0 :
			line += "\t"
		fieldtype = FindDataType(field)
		value = ConvertValueToType(values[I],fieldtype)
		if I == FldIdx and value > SerialKeys[SerKeyIdx][3] :
			SerialKeys[SerKeyIdx][3] = value
		I+=1
		line += value
	print line
	print "\\."
	print ""

		
# Handler for each line
def ProcessLine( line ):
	global HasPrinted, InCreateTable, NeedComma
	beg = strip( line )
	if ( beg[len(beg)-1:] == "," ) :
	  beg = beg[0:-1]
	if ( len(beg) == 0 ) :
	  return
	if ( beg[0] == "#" or beg[0:3] == "---" ) :
		Comment( beg )
		HasPrinted = False
	elif ( not InCreateTable and beg[0:12] == "CREATE TABLE" ) :
		BeginCreateTable( beg )
		InCreateTable = True
	elif ( InCreateTable and beg[0:1] == ")" ) :
		EndCreateTable( beg )
		InCreateTable = False	
		NeedComma = False
	elif ( InCreateTable ) :
		DoCreateTable( beg )
		NeedComma = True
	elif ( not InCreateTable and beg[0:11] == "INSERT INTO" ) :
		DoCopy(beg)
	elif not HasPrinted :
		HasPrinted = True
		print ""
		
		
# Out main function
def main():
	if (len(sys.argv) > 1 ) :
		File = LoadFile( sys.argv[1] )
		PrintFile( File )
		CloseFile( File )
	
# Start by calling main
main()
