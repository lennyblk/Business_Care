package com.businesscare;

import java.sql.*;

public class DatabaseManager {
    // Utilisez les mêmes paramètres que dans votre .env Laravel
    private static final String URL = "jdbc:mysql://127.0.0.1:3308/businesscare2";
    private static final String USER = "root";
    private static final String PASSWORD = "root";

    public Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }

    public boolean isDatabaseEmpty() {
        try (Connection conn = getConnection();
                Statement stmt = conn.createStatement()) {
            ResultSet rs = stmt.executeQuery("SELECT COUNT(*) FROM event_proposal");
            rs.next();
            return rs.getInt(1) == 0;
        } catch (SQLException e) {
            e.printStackTrace();
            return true;
        }
    }

    public void executeQuery(String query) throws SQLException {
        try (Connection conn = getConnection();
                Statement stmt = conn.createStatement()) {
            stmt.execute(query);
        }
    }

    public ResultSet getQueryResult(String query) throws SQLException {
        Connection conn = getConnection();
        Statement stmt = conn.createStatement();
        return stmt.executeQuery(query);
    }
}
