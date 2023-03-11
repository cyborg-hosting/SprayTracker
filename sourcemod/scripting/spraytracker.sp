/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//////////             Spray Tracker             ////////////
//////////                By Geit                ////////////
/////////////////////////////////////////////////////////////

#include <sourcemod>
#include <sdktools>

#pragma semicolon 1
#pragma newdecls required

/////////////////////////////////////////////////////////////
//////////////////////    SETUP    //////////////////////////
/////////////////////////////////////////////////////////////
#define PL_VERSION "1.12"

bool g_bRecent[MAXPLAYERS + 1] = { false };

Database g_hDatabase = null;

static const char g_sUpdateQuery[] = "UPDATE `sprays` SET `date` = NOW(), `name` = '%s', `ip` = '%s', `port` = '%s', `count` = `count` + 1 WHERE `steamid` = '%s' AND `filename` = '%s' LIMIT 1;";
static const char g_sSelectQuery[] = "SELECT 1 FROM `sprays` WHERE `steamid` = '%s' AND `filename` = '%s' AND `banned`;";
static const char g_sInsertQuery[] = "INSERT IGNORE INTO `sprays` (`steamid`, `name`, `ip`, `port`, `filename`, `firstdate`) VALUES ('%s', '%s', '%s', %s', '%s', NOW())";

public Plugin myinfo = 
{
    name = "Spray Tracker",
    author = "Geit",
    description = "Tracks spray names",
    version = PL_VERSION,
    url = "http://forums.alliedmods.net/showthread.php?t=127776"
}

public void OnPluginStart()
{
    DB_Init();
    CreateConVar("sm_spraytracker_version", PL_VERSION, "Spray 'n Display Version", FCVAR_SPONLY|FCVAR_REPLICATED|FCVAR_NOTIFY|FCVAR_DONTRECORD);
    AddTempEntHook("Player Decal", PlayerSpray);
}

Action PlayerSpray(const char[] te_name, const int[] clients, int numClients, float delay)
{
    float vec[3];
    TE_ReadVector("m_vecOrigin", vec);
    if(vec[0] == 0.0 && vec[1] == 0.0 && vec[2] == 0.0)
    {
        return Plugin_Continue;
    }

    int client = TE_ReadNum("m_nPlayer");

    if(client <= 0 || client > MaxClients)
    {
        return Plugin_Continue;
    }
    if(IsFakeClient(client))
    {
        return Plugin_Continue;
    }
    if(!IsClientInGame(client))
    {
        return Plugin_Continue;
    }
    if(!IsClientAuthorized(client))
    {
        return Plugin_Stop;
    }
    if(g_bRecent[client])
    {
        return Plugin_Stop;
    }

    char spray[PLATFORM_MAX_PATH];
    if(!GetPlayerDecalFile(client, spray, sizeof(spray)))
    {
        return Plugin_Continue;
    }

    g_bRecent[client] = true;

    char query[512];
    char name[80], steamid[32], ip[16], port[8];

    GetClientName(client, name, sizeof(name));
    GetClientAuthId(client, AuthId_Steam2, steamid, sizeof(steamid));
    FindConVar("hostip").GetString(ip, sizeof(ip));
    FindConVar("hostport").GetString(port, sizeof(port));

    g_hDatabase.Format(query, sizeof(query), g_sUpdateQuery, name, ip, port, steamid, spray);
    g_hDatabase.Query(DB_OnQuery, query, _, DBPrio_Low);

    g_hDatabase.Format(query, sizeof(query), g_sSelectQuery, steamid, spray);
    g_hDatabase.Query(DB_BanCheck, query, client, DBPrio_Normal);

    return Plugin_Continue;
}

void SprayDecal(int client, int entIndex, float pos[3]) 
{
    TE_Start("Player Decal");
    TE_WriteVector("m_vecOrigin", pos);
    TE_WriteNum("m_nEntity", entIndex);
    TE_WriteNum("m_nPlayer", client);
    TE_SendToAll();
}

public void OnClientPostAdminCheck(int client)
{
    if(IsFakeClient(client))
    {
        return;
    }

    char spray[PLATFORM_MAX_PATH];
    if(!GetPlayerDecalFile(client, spray, sizeof(spray)))
    {
        return;
    }

    char query[512];
    char name[80], steamid[32], ip[16], port[8];

    GetClientName(client, name, sizeof(name));
    GetClientAuthId(client, AuthId_Steam2, steamid, sizeof(steamid));
    FindConVar("hostip").GetString(ip, sizeof(ip));
    FindConVar("hostport").GetString(port, sizeof(port));

    g_hDatabase.Format(query, sizeof(query), g_sInsertQuery, steamid, name, ip, port, spray);
    g_hDatabase.Query(DB_OnQuery, query, _, DBPrio_Normal);
}

/////////////////////////////////////////////////////////////
/////////////////////////   SQL  ////////////////////////////
/////////////////////////////////////////////////////////////

void DB_Init()
{
    PrintToServer("[SprayTracker] connecting to the database...");

    if(SQL_CheckConfig("spray"))
    {
        Database.Connect(DB_OnConnect, "spray");
    }
    else
    {
        Database.Connect(DB_OnConnect);
    }
}

void DB_OnConnect(Database db, const char[] error, any data)
{
    if(db == null)
    {
        SetFailState("[SprayTracker] failed to connect to the database: %s", error);
    }
    PrintToServer("[SprayTracker] connected to the database.");

    SQL_FastQuery(db, "SET NAMES 'utf8mb4'");
    SQL_FastQuery(db, "SET CHARSET 'utf8mb4'");

    db.SetCharset("utf8mb4");

    g_hDatabase = db;
}

void DB_OnQuery(Database db, DBResultSet results, const char[] error, any data)
{
	if(db == null || results == null)
	{
		LogError("[SprayTracker] query failed: %s", error);
	}
}    

void DB_BanCheck(Database db, DBResultSet results, const char[] error, any data) 
{
    int client = view_as<int>(data);
    RequestFrame(SprayFrame, client);

    if(db == null || results == null)
    {
        LogError("[SprayTracker] query failed: %s", error);
        return;
    }

    if(results.RowCount >= 1)
    {
        SprayDecal(client, 0, NULL_VECTOR);
        PrintToChat(client, "You are not allowed to spray that.");
    }
}

void SprayFrame(any data)
{
    int client = view_as<int>(data);
    g_bRecent[client] = false;
}