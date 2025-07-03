package com.parkovisko.service;

import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.parkovisko.model.EstadoVehiculo;
import com.parkovisko.model.Vehiculo;
import com.parkovisko.repository.VehiculoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.stereotype.Service;

import java.io.ByteArrayOutputStream;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;

@Service
@RequiredArgsConstructor
public class ReporteService {

    private final VehiculoRepository vehiculoRepository;

    public byte[] generarReporteVehiculos(String marca, String modelo, EstadoVehiculo estado) {
        try (ByteArrayOutputStream baos = new ByteArrayOutputStream()) {
            PdfWriter writer = new PdfWriter(baos);
            PdfDocument pdf = new PdfDocument(writer);
            Document document = new Document(pdf);

            // Título del reporte
            document.add(new Paragraph("Reporte de Vehículos")
                    .setFontSize(20)
                    .setBold());

            // Filtros aplicados
            document.add(new Paragraph("Filtros aplicados:"));
            if (marca != null) document.add(new Paragraph("Marca: " + marca));
            if (modelo != null) document.add(new Paragraph("Modelo: " + modelo));
            if (estado != null) document.add(new Paragraph("Estado: " + estado));

            // Crear tabla
            Table table = new Table(new float[]{2, 2, 2, 2, 3, 2});
            table.addHeaderCell("Placa");
            table.addHeaderCell("Marca");
            table.addHeaderCell("Modelo");
            table.addHeaderCell("Color");
            table.addHeaderCell("Propietario");
            table.addHeaderCell("Estado");

            // Aplicar filtros
            Specification<Vehiculo> spec = Specification.where(null);
            if (marca != null) {
                spec = spec.and((root, query, cb) -> cb.equal(root.get("marca"), marca));
            }
            if (modelo != null) {
                spec = spec.and((root, query, cb) -> cb.equal(root.get("modelo"), modelo));
            }
            if (estado != null) {
                spec = spec.and((root, query, cb) -> cb.equal(root.get("estado"), estado));
            }

            // Obtener vehículos filtrados
            List<Vehiculo> vehiculos = vehiculoRepository.findAll(spec);

            // Agregar datos a la tabla
            for (Vehiculo v : vehiculos) {
                table.addCell(v.getPlaca());
                table.addCell(v.getMarca());
                table.addCell(v.getModelo());
                table.addCell(v.getColor());
                table.addCell(v.getPropietario().getNombre());
                table.addCell(v.getEstado().toString());
            }

            document.add(table);

            // Agregar fecha de generación
            document.add(new Paragraph("Reporte generado el: " + 
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm:ss"))));

            document.close();
            return baos.toByteArray();
        } catch (Exception e) {
            throw new RuntimeException("Error al generar el reporte", e);
        }
    }
} 