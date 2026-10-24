<x-admin.select name="agency_partner_id" label="Agency Penanggung Jawab" :options="$agencyOptions" :value="$propertyProject->agency_partner_id" placeholder="-- Tanpa agency --" />

@include('property-projects._fields')

