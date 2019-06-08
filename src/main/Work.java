package main;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.URL;
import java.nio.charset.Charset;
import java.sql.SQLException;

public class Work {

    private long timestamp;

    public Work(String world) {
        try {
            timestamp = System.currentTimeMillis() / 1000;

            MySQL.connect("host", "ip", "pass", "plemiona");

            MySQL.getStatement().executeUpdate("INSERT INTO `plemiona`.`data_pl"+world+"` (`id`, `timestamp`) VALUES (NULL, '"+timestamp+"')");
            MySQL.getStatement().executeUpdate("CREATE DATABASE pl"+world+"_"+timestamp+" DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci");

            MySQL.getStatement().executeUpdate("CREATE TABLE `pl"+world+"_"+timestamp+"`.`villages` (`id` INT(11) NOT NULL, `name` VARCHAR(255) NOT NULL, `owner` INT(11) NOT NULL, `x` INT(11) NOT NULL, `y` INT(11) NOT NULL, `points` INT(11) NOT NULL, `bonus` ENUM('all','barracks','farm','garage','iron','null','stable','stone','storage','wood') NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED");
            MySQL.getStatement().executeUpdate("ALTER TABLE `pl"+world+"_"+timestamp+"`.`villages` ADD PRIMARY KEY (`id`)");

            MySQL.getStatement().executeUpdate("CREATE TABLE `pl"+world+"_"+timestamp+"`.`allies` (`id` INT(11) NOT NULL, `name` VARCHAR(255) NOT NULL, `short` VARCHAR(255) NOT NULL, `points` INT(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED");
            MySQL.getStatement().executeUpdate("ALTER TABLE `pl"+world+"_"+timestamp+"`.`allies` ADD PRIMARY KEY (`id`)");

            MySQL.getStatement().executeUpdate("CREATE TABLE `pl"+world+"_"+timestamp+"`.`owners` (`id` INT(11) NOT NULL, `name` VARCHAR(255) NOT NULL, `points` INT(11) NOT NULL, `allie` INT(11) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED");
            MySQL.getStatement().executeUpdate("ALTER TABLE `pl"+world+"_"+timestamp+"`.`owners` ADD PRIMARY KEY (`id`)");

            for(int i = 0; i < 50; i ++) {
                printVillages(i, world);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void printVillages(int a, String world)  {
        System.out.println("======================================= "+a+"/50 =======================================");
        String url = "https://pl"+world+".plemiona.pl/map.php?v=2";

        for(int b = 0; b < 50; b++) {
            url += "&" + b*20 + "_" + a*20 + "=0";
        }

        try {
            JSONArray json = readJsonFromUrl(url);
            for(int i = 0; i < json.length(); i++) {
                JSONObject data = json.getJSONObject(i).getJSONObject("data");

                int o = data.getInt("x");
                int s = data.getInt("y");

                JSONObject vx = new JSONObject();

                if (data.get("villages") instanceof JSONObject) {
                    vx = data.getJSONObject("villages");
                } else {
                    JSONArray tmp = data.getJSONArray("villages");
                    for (int l = 0; l < tmp.length(); l++) {
                        vx.put(l + "", tmp.get(l));
                    }

                }

                JSONObject players = new JSONObject();
                JSONObject allies = new JSONObject();

                if(data.get("players") instanceof JSONObject) {
                    players = data.getJSONObject("players");
                }

                if(data.get("allies") instanceof  JSONObject) {
                    allies = data.getJSONObject("allies");
                }

                for (String keyX : vx.keySet()) {
                    JSONObject vy = new JSONObject();
                    if (vx.get(keyX) instanceof JSONObject) {
                        vy = vx.getJSONObject(keyX);
                    } else {
                        JSONArray tmp = vx.getJSONArray(keyX);
                        for (int l = 0; l < tmp.length(); l++) {
                            vy.put(l + "", tmp.get(l));
                        }
                    }

                    for (String keyY : vy.keySet()) {
                        JSONArray village = vy.getJSONArray(keyY);
                        int x = o + Integer.parseInt(keyX);
                        int y = s + Integer.parseInt(keyY);

                        String id = village.getString(0);
                        String name = village.get(2).toString();
                        int points = Integer.parseInt(village.getString(3).replace(".", ""));
                        String owner = village.getString(4);
                        int mods = village.getInt(5);

                        String bonus = village.get(6).toString();
                        if(!bonus.equals("null")) {
                            bonus = bonus.split("/")[1].split("\\.")[0];
                        }
                        String event = village.getString(7);
                        if (event.equals("0")) {
                            if(owner.equals("0") && name.equals("0")) name = "Wioska barbarzyńska";
                            if(!owner.equals("0")) {
                                String ownerName = players.getJSONArray(owner).getString(0);
                                String ownerPoints = players.getJSONArray(owner).getString(1).replace(".", "");
                                String allieID = players.getJSONArray(owner).getString(2);
                                MySQL.getStatement().executeUpdate("INSERT IGNORE INTO `pl"+world+"_"+timestamp+"`.`owners` (`id`, `name`, `points`, `allie`) VALUES ('"+owner+"', '"+ownerName+"', '"+ownerPoints+"', '"+allieID+"')");
                                if(!allieID.equals("0")) {
                                    String allie = allies.getJSONArray(allieID).getString(0);
                                    String alliePoints = allies.getJSONArray(allieID).getString(1).replace(".", "");
                                    String shortName = allies.getJSONArray(allieID).getString(2);
                                    MySQL.getStatement().executeUpdate("INSERT IGNORE INTO `pl"+world+"_"+timestamp+"`.`allies` (`id`, `name`, `short`, `points`) VALUES ('"+allieID+"', '"+allie+"', '"+shortName+"', '"+alliePoints+"')");
                                }
                            }
                            System.out.println("ID " + id + " X" + x + " Y" + y + " " + name + " " + points + " " + owner + " bonus " + bonus + " mods " + mods);
                            MySQL.getStatement().executeUpdate("INSERT INTO `pl"+world+"_"+timestamp+"`.`villages` (`id`, `name`, `owner`, `x`, `y`, `points`, `bonus`) VALUES ('"+id+"', '"+name+"', '"+owner+"', '"+x+"', '"+y+"', '"+points+"', '"+bonus+"')");
                        }
                    }

                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public JSONArray readJsonFromUrl(String url) throws IOException, JSONException {
        InputStream is = new URL(url).openStream();
        try {
            BufferedReader rd = new BufferedReader(new InputStreamReader(is, Charset.forName("UTF-8")));
            StringBuilder sb = new StringBuilder();
            int cp;
            while((cp = rd.read()) != -1) {
                sb.append((char) cp);
            }
            String jsonText = sb.toString();
            JSONArray json = new JSONArray(jsonText);
            return json;
        } finally {
            is.close();
        }
    }
}
