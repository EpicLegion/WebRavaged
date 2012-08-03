CREATE TABLE {dbp}users (
  id serial PRIMARY KEY,
  email varchar(127) NOT NULL UNIQUE,
  username varchar(127) NOT NULL UNIQUE,
  password char(50) NOT NULL,
  logins integer NOT NULL DEFAULT 0,
  last_login integer
);

CREATE TABLE {dbp}logs (
  id serial PRIMARY KEY,
  user_id integer NOT NULL REFERENCES {dbp}users ON DELETE CASCADE,
  date timestamp NOT NULL DEFAULT 'now',
  content text NOT NULL,
  ip varchar(15) NOT NULL DEFAULT '0.0.0.0'
);

CREATE TABLE {dbp}roles (
  id serial PRIMARY KEY,
  name varchar(32) NOT NULL UNIQUE,
  description varchar(255)
);

CREATE TABLE {dbp}roles_users (
  user_id integer NOT NULL REFERENCES {dbp}users ON DELETE CASCADE,
  role_id integer NOT NULL REFERENCES {dbp}roles ON DELETE CASCADE,
  PRIMARY KEY (user_id, role_id)
);

CREATE TABLE {dbp}servers (
  id serial PRIMARY KEY,
  name varchar(127) NOT NULL,
  ip varchar(15) NOT NULL DEFAULT '0.0.0.0',
  port integer NOT NULL DEFAULT 3074,
  password varchar(127) NOT NULL,
  game varchar(127) NOT NULL DEFAULT 'blackops',
  log_url varchar(255) NOT NULL
);

CREATE TABLE {dbp}templates (
  id serial PRIMARY KEY,
  name varchar(127) NOT NULL,
  game varchar(127) NOT NULL DEFAULT 'blackops',
  permissions integer NOT NULL DEFAULT 0
);

CREATE TABLE {dbp}leaderboards (
  guid integer NOT NULL,
  sid integer NOT NULL REFERENCES {dbp}servers ON DELETE CASCADE,
  kills integer DEFAULT 0,
  deaths integer DEFAULT 0,
  headshots integer DEFAULT 0,
  name varchar(255) NOT NULL,
  PRIMARY KEY (guid, sid)
);

CREATE TABLE {dbp}servers_users (
  server_id integer NOT NULL REFERENCES {dbp}servers ON DELETE CASCADE,
  user_id integer NOT NULL REFERENCES {dbp}users ON DELETE CASCADE,
  template_id integer REFERENCES {dbp}templates ON DELETE SET NULL,
  permissions integer NOT NULL DEFAULT 0,
  PRIMARY KEY (server_id, user_id)
);

CREATE TABLE {dbp}user_tokens (
  id serial PRIMARY KEY,
  user_id integer NOT NULL REFERENCES {dbp}users ON DELETE CASCADE,
  user_agent varchar(40) NOT NULL,
  token varchar(32) NOT NULL UNIQUE,
  created integer NOT NULL,
  expires integer NOT NULL
);

CREATE TABLE {dbp}messages (
  id serial PRIMARY KEY,
  user_id integer NOT NULL REFERENCES {dbp}users ON DELETE CASCADE,
  server_id integer NOT NULL REFERENCES {dbp}servers ON DELETE CASCADE,
  message text NOT NULL,
  current boolean NOT NULL DEFAULT FALSE
);

CREATE TABLE {dbp}players (
  id integer NOT NULL,
  server_id integer NOT NULL REFERENCES {dbp}servers ON DELETE CASCADE,
  ip_addresses text NOT NULL,
  names text NOT NULL,
  ping_total integer NOT NULL DEFAULT 0,
  ping_scans integer NOT NULL DEFAULT 0,
  ping_last integer NOT NULL DEFAULT 0,
  last_update integer NOT NULL DEFAULT 0,
  PRIMARY KEY (id, server_id)
);

CREATE TABLE {dbp}gamemodes (
  codename char(10) NOT NULL PRIMARY KEY,
  fullname varchar(20) NOT NULL
);

CREATE TABLE {dbp}gametypes (
  codename char(4) NOT NULL PRIMARY KEY,
  fullname varchar(20) NOT NULL
);

CREATE TYPE {dbp}gmode AS ENUM ('normal','hardcore','barebones','wager');
CREATE TYPE {dbp}gtype AS ENUM ('tdm','dm','sab','dem','ctf','sd','dom','koth','hlnd','gun','shrp','oic');
CREATE TYPE {dbp}gsize AS ENUM ('normal', 'small');

CREATE TABLE {dbp}playlists (
  id integer NOT NULL PRIMARY KEY,
  name varchar(50) NOT NULL,
  gametype_codename {dbp}gtype,
  gamemode_codename {dbp}gmode,
  size {dbp}gsize NOT NULL DEFAULT 'normal'
);

CREATE TABLE {dbp}servers_playlists (
  server_playlist_id serial,
  server_id integer NOT NULL REFERENCES {dbp}servers ON DELETE CASCADE,
  server_playlist_name varchar(200) NOT NULL,
  is_active smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (server_playlist_id, server_id),
  UNIQUE (server_id, server_playlist_name)
);

CREATE TABLE {dbp}custom_playlists (
  server_playlist_id integer NOT NULL,
  server_id integer NOT NULL,
  playlist_id integer NOT NULL,
  in_window smallint NOT NULL DEFAULT 0,
  last_set integer NOT NULL DEFAULT 0,
  PRIMARY KEY (server_playlist_id, server_id, playlist_id),
  FOREIGN KEY (server_playlist_id, server_id) REFERENCES {dbp}servers_playlists (server_playlist_id, server_id) ON DELETE CASCADE
);

INSERT INTO {dbp}roles (name, description) VALUES
('login', 'Login privileges, granted after account confirmation'),
('logs', 'Log management'),
('servers', 'Server management'),
('users', 'User management');

INSERT INTO {dbp}gamemodes (codename, fullname) VALUES
('barebones', 'Barebones Matches'),
('hardcore', 'Hardcore Matches'),
('normal', 'Normal Matches'),
('wager', 'Wager Matches');

INSERT INTO {dbp}gametypes (codename, fullname) VALUES
('ctf', 'Capture The Flag'),
('dem', 'Demolition'),
('dm', 'Free For All'),
('dom', 'Domination'),
('gun', 'Gun Game'),
('hlnd', 'Sticks And Stones'),
('koth', 'Headquarters'),
('oic', 'One In The Chamber'),
('sab', 'Sabotage'),
('sd', 'Search And Destroy'),
('shrp', 'Sharpshooter'),
('tdm', 'Team Deathmatch');

INSERT INTO {dbp}playlists (id, name, gametype_codename, gamemode_codename, size) VALUES
(1, 'Team Deathmatch', 'tdm', 'normal', 'normal'),
(2, 'Free For All', 'dm', 'normal', 'normal'),
(3, 'Capture The Flag', 'ctf', 'normal', 'normal'),
(4, 'Search And Destroy', 'sd', 'normal', 'normal'),
(5, 'Headquarters', 'koth', 'normal', 'normal'),
(6, 'Domination', 'dom', 'normal', 'normal'),
(7, 'Sabotage', 'sab', 'normal', 'normal'),
(8, 'Demolition', 'dem', 'normal', 'normal'),
(9, 'Hardcore Team Deathmatch', 'tdm', 'hardcore', 'normal'),
(10, 'Hardcore Free For All', 'dm', 'hardcore', 'normal'),
(11, 'Hardcore Capture The Flag', 'ctf', 'hardcore', 'normal'),
(12, 'Hardcore Search And Destroy', 'sd', 'hardcore', 'normal'),
(13, 'Hardcore Headquarters', 'koth', 'hardcore', 'normal'),
(14, 'Hardcore Domination', 'dom', 'hardcore', 'normal'),
(15, 'Hardcore Sabotage', 'sab', 'hardcore', 'normal'),
(16, 'Hardcore Demolition', 'dem', 'hardcore', 'normal'),
(17, 'Barebones Team Deathmatch', 'tdm', 'barebones', 'normal'),
(18, 'Barebones Free For All', 'dm', 'barebones', 'normal'),
(19, 'Barebones Capture The Flag', 'ctf', 'barebones', 'normal'),
(20, 'Barebones Search And Destroy', 'sd', 'barebones', 'normal'),
(21, 'Barebones Headquarters', 'koth', 'barebones', 'normal'),
(22, 'Barebones Domination', 'dom', 'barebones', 'normal'),
(23, 'Barebones Sabotage', 'sab', 'barebones', 'normal'),
(24, 'Barebones Demolition', 'dem', 'barebones', 'normal'),
(25, 'Team Tactical', NULL, 'normal', 'normal'),
(26, 'One In The Chamber', 'oic', 'wager', 'normal'),
(27, 'Sticks And Stones', 'hlnd', 'wager', 'normal'),
(28, 'Gun Game', 'gun', 'wager', 'normal'),
(29, 'Sharpshooter', 'shrp', 'wager', 'normal'),
(30, 'Hardcore Team Tactical', NULL, 'hardcore', 'normal'),
(31, 'Barebones Team Tactical', NULL, 'barebones', 'normal'),
(32, 'Team Deathmatch 12 players', 'tdm', 'normal', 'small'),
(33, 'Free For All 12 players', 'dm', 'normal', 'small'),
(34, 'Capture The Flag 12 players', 'ctf', 'normal', 'small'),
(35, 'Search And Destroy 12 players', 'sd', 'normal', 'small'),
(36, 'Headquarters 12 players', 'koth', 'normal', 'small'),
(37, 'Domination 12 players', 'dom', 'normal', 'small'),
(38, 'Sabotage 12 players', 'sab', 'normal', 'small'),
(39, 'Demolition 12 players', 'dem', 'normal', 'small'),
(40, 'Team Tactical 12 players', NULL, 'normal', 'small'),
(41, 'Hardcore Team Deathmatch 12 players', 'tdm', 'hardcore', 'small'),
(42, 'Hardcore Free For All 12 players', 'dm', 'hardcore', 'small'),
(43, 'Hardcore Capture The Flag 12 players', 'ctf', 'hardcore', 'small'),
(44, 'Hardcore Search And Destroy 12 players', 'sd', 'hardcore', 'small'),
(45, 'Hardcore Headquarters 12 players', 'koth', 'hardcore', 'small'),
(46, 'Hardcore Domination 12 players', 'dom', 'hardcore', 'small'),
(47, 'Hardcore Sabotage 12 players', 'sab', 'hardcore', 'small'),
(48, 'Hardcore Demolition 12 players', 'dem', 'hardcore', 'small'),
(49, 'Hardcore Team Tactical 12 players', NULL, 'hardcore', 'small'),
(50, 'Barebones Team Deathmatch 12 players', 'tdm', 'barebones', 'small'),
(51, 'Barebones Free For All 12 players', 'dm', 'barebones', 'small'),
(52, 'Barebones Capture The Flag 12 players', 'ctf', 'barebones', 'small'),
(53, 'Barebones Search And Destroy 12 players', 'sd', 'barebones', 'small'),
(54, 'Barebones Headquarters 12 players', 'koth', 'barebones', 'small'),
(55, 'Barebones Domination 12 players', 'dom', 'barebones', 'small'),
(56, 'Barebones Sabotage 12 players', 'sab', 'barebones', 'small'),
(57, 'Barebones Team Tactical 12 players', NULL, 'barebones', 'small');

CREATE INDEX {dbp}logs_user_id_index ON {dbp}logs (user_id);
CREATE INDEX {dbp}roles_users_user_id_index ON {dbp}roles_users (user_id);
CREATE INDEX {dbp}leaderboards_sid_index ON {dbp}leaderboards (sid);
CREATE INDEX {dbp}servers_users_user_id_index ON {dbp}servers_users (user_id);
CREATE INDEX {dbp}user_tokens_user_id_index ON {dbp}user_tokens (user_id);
CREATE INDEX {dbp}messages_user_id_index ON {dbp}messages (user_id);
CREATE INDEX {dbp}messages_server_id_index ON {dbp}messages (server_id);
CREATE INDEX {dbp}players_server_id_index ON {dbp}players (server_id);