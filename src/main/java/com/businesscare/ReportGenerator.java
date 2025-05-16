package com.businesscare;

import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Image;
import com.itextpdf.io.image.ImageDataFactory;
import org.jfree.chart.ChartFactory;
import org.jfree.chart.JFreeChart;
import org.jfree.chart.ChartUtils;
import org.jfree.data.category.DefaultCategoryDataset;
import org.jfree.data.general.DefaultPieDataset;
import org.jfree.chart.axis.NumberAxis;

import java.sql.ResultSet;
import java.awt.image.BufferedImage;
import java.io.FileOutputStream;
import java.io.File;

public class ReportGenerator {
    private final DatabaseManager dbManager;

    public ReportGenerator() {
        this.dbManager = new DatabaseManager();
    }

    public void generateReport(String type) throws Exception {
        String query = switch (type.toLowerCase()) {
            case "histogram" -> """
                    SELECT st.title as activity_type, COUNT(*) as count
                    FROM event_proposal ep
                    JOIN service_type st ON ep.event_type_id = st.id
                    GROUP BY st.title""";
            case "pie" -> """
                    SELECT status, COUNT(*) as count
                    FROM event_proposal
                    GROUP BY status""";
            case "line" -> """
                    SELECT DATE(created_at) as date, COUNT(*) as count
                    FROM event_proposal
                    GROUP BY DATE(created_at)
                    ORDER BY date""";
            case "subscriptions" -> """
                    SELECT formule_abonnement, COUNT(*) as count
                    FROM company
                    GROUP BY formule_abonnement""";
            case "revenue" -> """
                    SELECT DATE_FORMAT(issue_date, '%Y-%m') as month,
                           SUM(total_amount) as total
                    FROM invoice
                    WHERE payment_status = 'Paid'
                    GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
                    ORDER BY month""";
            case "top_companies" -> """
                    SELECT c.name, COUNT(e.id) as employee_count
                    FROM company c
                    LEFT JOIN employee e ON c.id = e.company_id
                    GROUP BY c.id, c.name
                    ORDER BY employee_count DESC
                    LIMIT 5""";
            case "service_types" -> """
                    SELECT st.title, COUNT(*) as count
                    FROM service_type st
                    JOIN event_proposal ep ON st.id = ep.event_type_id
                    GROUP BY st.title""";
            case "provider_events" -> """
                    SELECT p.first_name, p.last_name, COUNT(pa.id) as event_count
                    FROM provider p
                    LEFT JOIN provider_assignment pa ON p.id = pa.provider_id
                    GROUP BY p.id
                    ORDER BY event_count DESC
                    LIMIT 5""";
            case "activity_proposition_formula" -> """
                    SELECT
                        c.formule_abonnement,
                        COUNT(ep.id) as nombre_activites
                    FROM company c
                    LEFT JOIN event_proposal ep ON c.id = ep.company_id
                    GROUP BY c.formule_abonnement
                    ORDER BY nombre_activites DESC""";
            case "event_types_distribution" -> """
                    SELECT e.event_type, COUNT(*) as count
                    FROM event e
                    GROUP BY e.event_type""";
            case "service_costs" -> """
                    SELECT
                        st.title as service_type,
                        SUM(pa.payment_amount) as total_cost,
                        COUNT(*) as count,
                        AVG(pa.payment_amount) as avg_cost
                    FROM provider_assignment pa
                    JOIN event_proposal ep ON pa.event_proposal_id = ep.id
                    JOIN service_type st ON ep.event_type_id = st.id
                    WHERE pa.status = 'Accepted'
                    GROUP BY st.id, st.title
                    ORDER BY total_cost DESC""";
            case "top_services" -> """
                    SELECT st.title, COUNT(ep.id) as count
                    FROM service_type st
                    JOIN event_proposal ep ON st.id = ep.event_type_id
                    JOIN provider_assignment pa ON ep.id = pa.event_proposal_id
                    WHERE pa.status = 'Accepted'
                    GROUP BY st.id, st.title
                    ORDER BY count DESC
                    LIMIT 5""";
            default -> throw new IllegalArgumentException("Type non supporté: " + type);
        };

        ResultSet data = dbManager.getQueryResult(query);

        String filename = "report_" + type + ".pdf";
        try (PdfWriter writer = new PdfWriter(new FileOutputStream(filename));
                PdfDocument pdf = new PdfDocument(writer);
                Document document = new Document(pdf)) {

            document.add(new Paragraph("Rapport des activités"));

            switch (type.toLowerCase()) {
                case "histogram":
                    addHistogramChart(document, data);
                    break;
                case "pie":
                    addPieChart(document, data);
                    break;
                case "line":
                    addLineChart(document, data);
                    break;
                case "subscriptions":
                    addSubscriptionsChart(document, data);
                    break;
                case "revenue":
                    addRevenueChart(document, data);
                    break;
                case "top_companies":
                    addTopCompaniesChart(document, data);
                    break;
                case "service_types":
                    addServiceTypesChart(document, data);
                    break;
                case "provider_events":
                    addProviderEventsChart(document, data);
                    break;
                case "activity_proposition_formula":
                    addContractsStatusChart(document, data);
                    break;
                case "event_types_distribution":
                    addEventTypesDistributionChart(document, data);
                    break;
                case "service_costs":
                    addServiceCostsChart(document, data);
                    break;
                case "top_services":
                    addTopServicesChart(document, data);
                    break;
            }
        }
        System.out.println("Rapport généré: " + filename);
    }

    public void generatePageContent(Document document, String type) throws Exception {
        String query = switch (type.toLowerCase()) {
            case "histogram" -> """
                    SELECT st.title as activity_type, COUNT(*) as count
                    FROM event_proposal ep
                    JOIN service_type st ON ep.event_type_id = st.id
                    GROUP BY st.title""";
            case "pie" -> """
                    SELECT status, COUNT(*) as count
                    FROM event_proposal
                    GROUP BY status""";
            case "line" -> """
                    SELECT DATE(created_at) as date, COUNT(*) as count
                    FROM event_proposal
                    GROUP BY DATE(created_at)
                    ORDER BY date""";
            case "subscriptions" -> """
                    SELECT formule_abonnement, COUNT(*) as count
                    FROM company
                    GROUP BY formule_abonnement""";
            case "revenue" -> """
                    SELECT DATE_FORMAT(issue_date, '%Y-%m') as month,
                           SUM(total_amount) as total
                    FROM invoice
                    WHERE payment_status = 'Paid'
                    GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
                    ORDER BY month""";
            case "top_companies" -> """
                    SELECT c.name, COUNT(e.id) as employee_count
                    FROM company c
                    LEFT JOIN employee e ON c.id = e.company_id
                    GROUP BY c.id, c.name
                    ORDER BY employee_count DESC
                    LIMIT 5""";
            case "service_types" -> """
                    SELECT st.title, COUNT(*) as count
                    FROM service_type st
                    JOIN event_proposal ep ON st.id = ep.event_type_id
                    GROUP BY st.title""";
            case "provider_events" -> """
                    SELECT p.first_name, p.last_name, COUNT(pa.id) as event_count
                    FROM provider p
                    LEFT JOIN provider_assignment pa ON p.id = pa.provider_id
                    GROUP BY p.id
                    ORDER BY event_count DESC
                    LIMIT 5""";
            case "activity_proposition_formula" -> """
                    SELECT
                        c.formule_abonnement,
                        COUNT(ep.id) as nombre_activites
                    FROM company c
                    LEFT JOIN event_proposal ep ON c.id = ep.company_id
                    GROUP BY c.formule_abonnement
                    ORDER BY nombre_activites DESC""";
            case "event_types_distribution" -> """
                    SELECT e.event_type, COUNT(*) as count
                    FROM event e
                    GROUP BY e.event_type""";
            case "service_costs" -> """
                    SELECT
                        st.title as service_type,
                        SUM(pa.payment_amount) as total_cost,
                        COUNT(*) as count,
                        AVG(pa.payment_amount) as avg_cost
                    FROM provider_assignment pa
                    JOIN event_proposal ep ON pa.event_proposal_id = ep.id
                    JOIN service_type st ON ep.event_type_id = st.id
                    WHERE pa.status = 'Accepted'
                    GROUP BY st.id, st.title
                    ORDER BY total_cost DESC""";
            case "top_services" -> """
                    SELECT st.title, COUNT(ep.id) as count
                    FROM service_type st
                    JOIN event_proposal ep ON st.id = ep.event_type_id
                    JOIN provider_assignment pa ON ep.id = pa.event_proposal_id
                    WHERE pa.status = 'Accepted'
                    GROUP BY st.id, st.title
                    ORDER BY count DESC
                    LIMIT 5""";
            default -> throw new IllegalArgumentException("Type non supporté: " + type);
        };

        ResultSet data = dbManager.getQueryResult(query);

        switch (type.toLowerCase()) {
            case "histogram" -> addHistogramChart(document, data);
            case "pie" -> addPieChart(document, data);
            case "line" -> addLineChart(document, data);
            case "subscriptions" -> addSubscriptionsChart(document, data);
            case "revenue" -> addRevenueChart(document, data);
            case "top_companies" -> addTopCompaniesChart(document, data);
            case "service_types" -> addServiceTypesChart(document, data);
            case "provider_events" -> addProviderEventsChart(document, data);
            case "activity_proposition_formula" -> addContractsStatusChart(document, data);
            case "event_types_distribution" -> addEventTypesDistributionChart(document, data);
            case "service_costs" -> addServiceCostsChart(document, data);
            case "top_services" -> addTopServicesChart(document, data);
        }
    }

    private void addHistogramChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            dataset.addValue(data.getInt("count"), "Activités", data.getString("activity_type"));
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Distribution des types d'activités",
                "Type d'activité",
                "Nombre de demandes",
                dataset);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addPieChart(Document document, ResultSet data) throws Exception {
        DefaultPieDataset dataset = new DefaultPieDataset();
        while (data.next()) {
            String status = switch (data.getString("status")) {
                case "Pending" -> "En attente";
                case "Assigned" -> "Assigné";
                case "Accepted" -> "Accepté";
                case "Rejected" -> "Rejeté";
                case "Completed" -> "Terminé";
                default -> data.getString("status");
            };
            dataset.setValue(status, data.getInt("count"));
        }

        JFreeChart chart = ChartFactory.createPieChart(
                "État des demandes d'activités",
                dataset,
                true, // légende
                true, // tooltips
                false // URLs
        );

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);

        document.add(new Paragraph("Répartition des états des demandes d'activités").setBold());
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        document.add(
                new Paragraph("Ce graphique montre la distribution des différents états des demandes d'activités."));

        tempFile.delete();
    }

    private void addLineChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        int totalCount = 0;
        while (data.next()) {
            int count = data.getInt("count");
            totalCount += count;
            dataset.addValue(count, "Nombre de demandes", data.getString("date"));
        }

        JFreeChart chart = ChartFactory.createLineChart(
                "Évolution des demandes d'activités",
                "Date",
                "Nombre de demandes",
                dataset);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);

        document.add(new Paragraph("Évolution temporelle des demandes").setBold());
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        document.add(new Paragraph(String.format(
                "Total des demandes: %d\nCe graphique montre l'évolution du nombre de demandes au fil du temps.",
                totalCount)));

        tempFile.delete();
    }

    private void addSubscriptionsChart(Document document, ResultSet data) throws Exception {
        DefaultPieDataset dataset = new DefaultPieDataset();
        while (data.next()) {
            dataset.setValue(data.getString("formule_abonnement"), data.getInt("count"));
        }

        JFreeChart chart = ChartFactory.createPieChart(
                "Répartition par formule d'abonnement",
                dataset,
                true,
                true,
                false);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addRevenueChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();

        // Créer un tableau pour stocker les revenus par mois
        double[] monthlyRevenues = new double[12];
        while (data.next()) {
            String monthStr = data.getString("month");
            int monthIndex = Integer.parseInt(monthStr.split("-")[1]) - 1; // Le mois dans la BD est 1-12
            monthlyRevenues[monthIndex] = data.getDouble("total");
        }

        // Ajouter tous les mois au dataset, même ceux sans revenus
        String[] months = { "Jan", "Fév", "Mars", "Avr", "Mai", "Juin",
                "Jui", "Août", "Sep", "Oct", "Nov", "Déc" };
        for (int i = 0; i < 12; i++) {
            dataset.addValue(monthlyRevenues[i], "CA", months[i]);
        }

        JFreeChart chart = ChartFactory.createLineChart(
                "Évolution du CA par mois",
                "Mois",
                "CA (€)",
                dataset);

        // Personnalisation de l'axe Y pour avoir des montants arrondis
        NumberAxis rangeAxis = (NumberAxis) chart.getCategoryPlot().getRangeAxis();
        rangeAxis.setStandardTickUnits(NumberAxis.createIntegerTickUnits());

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        document.add(new Paragraph("Évolution du chiffre d'affaires").setBold());
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addTopCompaniesChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            dataset.addValue(data.getInt("employee_count"), "Employés", data.getString("name"));
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Top 5 des clients par nombre d'employés",
                "Client",
                "Nombre d'employés",
                dataset);

        // Personnalisation de l'axe Y pour n'avoir que des entiers
        NumberAxis rangeAxis = (NumberAxis) chart.getCategoryPlot().getRangeAxis();
        rangeAxis.setStandardTickUnits(NumberAxis.createIntegerTickUnits());

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addServiceTypesChart(Document document, ResultSet data) throws Exception {
        DefaultPieDataset dataset = new DefaultPieDataset();
        while (data.next()) {
            dataset.setValue(data.getString("title"), data.getInt("count"));
        }

        JFreeChart chart = ChartFactory.createPieChart(
                "Répartition par type de prestation",
                dataset,
                true,
                true,
                false);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addProviderEventsChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            String providerName = data.getString("first_name") + " " + data.getString("last_name");
            dataset.addValue(data.getInt("event_count"), "Événements", providerName);
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Nombre d'événements par prestataire",
                "Prestataire",
                "Nombre d'événements",
                dataset);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addContractsStatusChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            dataset.addValue(
                    data.getInt("nombre_activites"),
                    "Nombre d'activités",
                    data.getString("formule_abonnement"));
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Nombre d'activités par formule d'abonnement",
                "Formule",
                "Nombre d'activités",
                dataset);

        NumberAxis rangeAxis = (NumberAxis) chart.getCategoryPlot().getRangeAxis();
        rangeAxis.setStandardTickUnits(NumberAxis.createIntegerTickUnits());

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        document.add(new Paragraph("Distribution des activités par formule").setBold());
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        document.add(new Paragraph(
                "Ce graphique montre le nombre total d'activités demandées par les clients de chaque formule d'abonnement."));
        tempFile.delete();
    }

    private void addEventTypesDistributionChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            dataset.addValue(data.getInt("count"), "Événements", data.getString("event_type"));
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Distribution des types d'événements",
                "Type d'événement",
                "Nombre d'événements",
                dataset);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addServiceCostsChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            String serviceType = data.getString("service_type");
            double totalCost = data.getDouble("total_cost");
            double avgCost = data.getDouble("avg_cost");
            int count = data.getInt("count");

            dataset.addValue(totalCost, "Coût total", serviceType);
            dataset.addValue(avgCost, "Coût moyen", serviceType);
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Répartition des coûts par type de service",
                "Type de service",
                "Coût (€)",
                dataset);

        // Personnalisation de l'axe Y pour avoir des montants arrondis
        NumberAxis rangeAxis = (NumberAxis) chart.getCategoryPlot().getRangeAxis();
        rangeAxis.setStandardTickUnits(NumberAxis.createIntegerTickUnits());

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        document.add(new Paragraph("Analyse des coûts par type de service").setBold());
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }

    private void addTopServicesChart(Document document, ResultSet data) throws Exception {
        DefaultCategoryDataset dataset = new DefaultCategoryDataset();
        while (data.next()) {
            dataset.addValue(data.getInt("count"), "Services", data.getString("title"));
        }

        JFreeChart chart = ChartFactory.createBarChart(
                "Top 5 des services les plus demandés",
                "Service",
                "Nombre de demandes",
                dataset);

        File tempFile = File.createTempFile("chart", ".png");
        ChartUtils.saveChartAsPNG(tempFile, chart, 500, 300);
        Image img = new Image(ImageDataFactory.create(tempFile.getAbsolutePath()));
        document.add(img);
        tempFile.delete();
    }
}
