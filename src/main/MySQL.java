package main;

import java.sql.*;

public class MySQL {

	private static Connection connection;

	public static void connect(String server, String user, String password, String database) {
		try {
			connection = DriverManager.getConnection("jdbc:mysql://"+server+":3306/"+database+"?autoReconnect=true&useUnicode=true&characterEncoding=utf-8&useSSL=false&useUnicode=true&useJDBCCompliantTimezoneShift=true&useLegacyDatetimeCode=false&serverTimezone=UTC", user, password);
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}

	public static Statement getStatement() {
        try {
			return connection.createStatement();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return null;
	}

	public static void close() {
		try {
			connection.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
}
