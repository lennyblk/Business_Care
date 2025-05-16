package com.businesscare;

import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.AreaBreak;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.kernel.geom.PageSize;

public class CompleteReportGenerator {
    private final ReportGenerator reportGenerator;

    public CompleteReportGenerator() {
        this.reportGenerator = new ReportGenerator();
    }

    public void generateCompleteReport() throws Exception {
        try (PdfWriter writer = new PdfWriter("rapport_complet.pdf");
                PdfDocument pdf = new PdfDocument(writer);
                Document document = new Document(pdf, PageSize.A4)) {

            // Page 1 - Comptes clients
            document.add(new Paragraph("Statistiques des comptes clients").setBold().setFontSize(16));
            reportGenerator.generatePageContent(document, "subscriptions");
            reportGenerator.generatePageContent(document, "revenue");
            reportGenerator.generatePageContent(document, "top_companies");
            reportGenerator.generatePageContent(document, "activity_proposition_formula");

            // Page 2 - Événements
            document.add(new AreaBreak());
            document.add(new Paragraph("Statistiques des événements").setBold().setFontSize(16));
            reportGenerator.generatePageContent(document, "histogram");
            reportGenerator.generatePageContent(document, "pie");
            reportGenerator.generatePageContent(document, "line");
            reportGenerator.generatePageContent(document, "event_types_distribution");

            // Page 3 - Prestations
            document.add(new AreaBreak());
            document.add(new Paragraph("Statistiques des prestations").setBold().setFontSize(16));
            reportGenerator.generatePageContent(document, "service_types");
            reportGenerator.generatePageContent(document, "service_costs");
            reportGenerator.generatePageContent(document, "provider_events");
            reportGenerator.generatePageContent(document, "top_services");

            System.out.println("Rapport complet généré : rapport_complet.pdf");
        }
    }
}
