package main

import (
	"fmt"
	"math"
	"os"
	"slices"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")

		reports := make([][]int, len(lines))

		for index, line := range lines {
			report := strings.Fields(line)
			reportInt := make([]int, len(report))
			for index, stringValue := range report {
				reportInt[index], _ = strconv.Atoi(stringValue)
			}
			reports[index] = reportInt
		}
		var safeReports int
		for _, report := range reports {
			if isReportSafeWithDampener(report) {
				safeReports++
			}
		}

		println(safeReports)
	}
}
func isReportSafeWithDampener(report []int) bool {
	if isReportSafe(report) {
		return true
	}
	for index := 0; index < len(report); index++ {
		dampenedReport := make([]int, len(report))
		copy(dampenedReport, report)
		dampenedReport = slices.Delete(dampenedReport, index, index+1)
		if isReportSafe(dampenedReport) {
			return true
		}
	}
	return false
}
func isReportSafe(report []int) bool {
	if report[1] == report[0] {
		return false
	}
	var isIncreasing = report[1] > report[0]
	for index := 0; index < len(report)-1; index++ {
		differenceAbs := int(math.Abs(float64(report[index+1] - report[index])))
		if differenceAbs > 3 || differenceAbs < 1 {
			return false
		}
		difference := report[index+1] - report[index]
		if isIncreasing && difference < 0 {
			return false
		}
		if !isIncreasing && difference > 0 {
			return false
		}
	}

	return true
}
