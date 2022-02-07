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
#define PL_VERSION "1.11gfix"

bool g_Recent[MAXPLAYERS + 1] = { false };

Handle g_StatsDB = null;

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
    Stats_Init();
    CreateConVar("sm_spraytracker_version", PL_VERSION, "Spray 'n Display Version", FCVAR_SPONLY|FCVAR_REPLICATED|FCVAR_NOTIFY|FCVAR_DONTRECORD);
    AddTempEntHook("Player Decal", PlayerSpray);
}


public Action PlayerSpray(const char[] te_name, const int[] clients, int client_count, float delay)
{
    int client = TE_ReadNum("m_nPlayer");

    if(client && IsClientInGame(client) && g_Recent[client] == false) 
    {
        char spray[96];
        if(!GetPlayerDecalFile(client, spray, sizeof(spray)))
        {
            return Plugin_Continue;
        }
        
        char query[392], name[96], nbuffer[256], port[8]/*, buffer[256], steamid[25]*/, spraybuffer[256], ip[32];
        
        GetConVarString(FindConVar("hostport"), port, sizeof(port));
        GetConVarString(FindConVar("ip"), ip, sizeof(ip));
        GetClientName(client, name, sizeof(name));
        SQL_EscapeString(g_StatsDB, name, nbuffer, sizeof(nbuffer));
        
        SQL_EscapeString(g_StatsDB, spray, spraybuffer, sizeof(spraybuffer));
        
        Format(query, sizeof(query), "UPDATE sprays SET date=NOW(), name='%s', port='%s', ip='%s', count = count + 1 WHERE filename='%s' LIMIT 1", nbuffer, port, ip, spraybuffer);
        SQL_TQuery(g_StatsDB, T_ErrorOnly, query);
        
        Format(query, sizeof(query), "SELECT banned from sprays WHERE filename = '%s' LIMIT 1", spraybuffer);
        SQL_TQuery(g_StatsDB, CB_BanCheck, query, client);
        
        g_Recent[client] = true;
        CreateTimer(2.0, Recent_Spray, client);
    }

    return Plugin_Continue;
}

public void SprayDecal(int client, int entIndex, float pos[3]) 
{
    TE_Start("Player Decal");
    TE_WriteVector("m_vecOrigin", pos);
    TE_WriteNum("m_nEntity", entIndex);
    TE_WriteNum("m_nPlayer", client);
    TE_SendToAll();
}

public Action Recent_Spray(Handle timer, any client) 
{
    g_Recent[client] = false;
}
/////////////////////////////////////////////////////////////
//////////////////////   CALLBACKS  /////////////////////////
/////////////////////////////////////////////////////////////
public void CB_BanCheck(Handle owner, Handle result, const char[] error, any client) 
{
    if(result != null && SQL_HasResultSet(result) && SQL_GetRowCount(result) >= 1) 
    {
        while(SQL_FetchRow(result))
        {
            if (SQL_FetchInt(result, 0) == 1) 
            {
                SprayDecal(client, 0, NULL_VECTOR);
                PrintToChat(client, "You're not allowed to spray that!");
            }
        }
    }
}

public void OnClientPostAdminCheck(int client)
{
    char spray[96];
    
    if(IsClientInGame(client) && !IsFakeClient(client) && DatabaseIntact())
    {
        if(!GetPlayerDecalFile(client, spray, sizeof(spray)))
            return;
        
        char query[392], steamid[25], name[96], spraybuffer[256], buffer[256], nbuffer[256], port[8], ip[32];
    
        GetConVarString(FindConVar("hostport"), port, sizeof(port));
        GetConVarString(FindConVar("ip"), ip, sizeof(ip));
        GetClientAuthId(client, AuthId_Steam2, steamid, sizeof(steamid));
        GetClientName(client, name, sizeof(name));
        
        SQL_EscapeString(g_StatsDB, steamid, buffer, sizeof(buffer));
        SQL_EscapeString(g_StatsDB, name, nbuffer, sizeof(nbuffer));
        SQL_EscapeString(g_StatsDB, spray, spraybuffer, sizeof(spraybuffer));
        
        Format(query, sizeof(query), "INSERT IGNORE INTO `sprays` (`steamid`, `name`, `port`, `filename`, `firstdate`, `ip`) VALUES('%s', '%s', '%s', '%s', NOW(), '%s')", buffer, nbuffer, port, spraybuffer, ip);
        SQL_TQuery(g_StatsDB, T_ErrorOnly, query);
    }
}

/////////////////////////////////////////////////////////////
/////////////////////////   SQL  ////////////////////////////
/////////////////////////////////////////////////////////////

public bool DatabaseIntact()
{
    if(g_StatsDB != null)
    {
        return true;
    }
    else 
    {
        char error[255];
        SQL_GetError(g_StatsDB, error, sizeof(error));
        PrintToServer("Database not intact (%s)", error);
        return false;
    }
}

public void T_ErrorOnly(Handle owner, Handle result, const char[] error, any client)
{
    if(result == null)
    {
        LogError("[SPRAY] MYSQL ERROR (error: %s)", error);
    }
}

stock void Stats_Init()
{
    char error[255];
    PrintToServer("Connecting to database...");
    g_StatsDB = SQL_Connect("spray", true, error, sizeof(error));
    
    if(g_StatsDB != null)
    {
        SQL_TQuery(g_StatsDB, T_ErrorOnly, "SET NAMES UTF8", 0, DBPrio_High);
        PrintToServer("Connected successfully.");
    }
    else 
    {
        PrintToServer("Connection Failure!");
        LogError("[SPRAY] MYSQL ERROR (error: %s)", error);
    }
}