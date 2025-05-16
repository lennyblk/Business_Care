package com.businesscare;

import com.github.javafaker.Faker;
import java.sql.SQLException;
import java.sql.Date;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public class DataGenerator {
    private final DatabaseManager dbManager;
    private final Faker faker;

    public DataGenerator(DatabaseManager dbManager) {
        this.dbManager = dbManager;
        this.faker = new Faker();
    }

    public void generateRandomData() throws SQLException {
        if (!dbManager.isDatabaseEmpty()) {
            System.out.println("La base de données n'est pas vide. Génération ignorée.");
            return;
        }

        for (int i = 0; i < 30; i++) {
            java.sql.Date proposedDate = new java.sql.Date(
                    faker.date().future(90, java.util.concurrent.TimeUnit.DAYS).getTime());

            String eventQuery = String.format(
                    "INSERT INTO event_proposal (company_id, event_type_id, proposed_date, location_id, status, notes, duration) VALUES (%d, %d, '%s', %d, '%s', '%s', %d)",
                    faker.number().numberBetween(1, 5),
                    faker.number().numberBetween(1, 3),
                    proposedDate,
                    faker.number().numberBetween(1, 5),
                    getRandomStatus(),
                    faker.company().catchPhrase(),
                    faker.number().numberBetween(30, 240));
            dbManager.executeQuery(eventQuery);
        }
    }

    private String getRandomStatus() {
        String[] statuses = { "Pending", "Assigned", "Accepted", "Rejected", "Completed" };
        return statuses[faker.number().numberBetween(0, statuses.length)];
    }
}
